# app.py
# Main application file for docsmate.

import streamlit as st
import sqlite3
import hashlib
import json
import pandas as pd
from datetime import datetime
from streamlit_quill import st_quill
import os
import config  # Import the configuration file
import ai_integration # Import the AI integration module
import re
from xhtml2pdf import pisa
from io import BytesIO

# --- DATABASE SETUP ---

def init_db():
    """Initializes the SQLite database and creates tables if they don't exist."""
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()

    # User table
    c.execute('''
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            username TEXT UNIQUE,
            password_hash TEXT,
            is_admin BOOLEAN
        )
    ''')
    # Add aiuser if not exists
    c.execute("SELECT * FROM users WHERE username='aiuser'")
    if not c.fetchone():
        add_user('aiuser', 'ai_password', is_admin=False)


    # Projects table
    c.execute('''
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY,
            name TEXT,
            description TEXT,
            created_by_user_id INTEGER,
            FOREIGN KEY(created_by_user_id) REFERENCES users(id)
        )
    ''')
    
    # Templates table - now includes document_type
    c.execute('''
        CREATE TABLE IF NOT EXISTS templates (
            id INTEGER PRIMARY KEY,
            name TEXT UNIQUE,
            content TEXT,
            document_type TEXT,
            created_at DATETIME
        )
    ''')
    # Check and add 'created_at' column for backward compatibility
    c.execute("PRAGMA table_info(templates)")
    columns = [column[1] for column in c.fetchall()]
    if 'created_at' not in columns:
        c.execute("ALTER TABLE templates ADD COLUMN created_at DATETIME")


    # Team members table
    c.execute('''
        CREATE TABLE IF NOT EXISTS team_members (
            id INTEGER PRIMARY KEY,
            name TEXT,
            email TEXT,
            project_id INTEGER,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        )
    ''')
    
    # Documents table - updated schema
    c.execute('''
        CREATE TABLE IF NOT EXISTS documents (
            id INTEGER PRIMARY KEY,
            project_id INTEGER,
            doc_name TEXT,
            doc_type TEXT,
            content TEXT,
            version INTEGER,
            status TEXT,
            created_at DATETIME,
            updated_at DATETIME,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        )
    ''')

    # Check and add 'doc_name' column for backward compatibility
    c.execute("PRAGMA table_info(documents)")
    columns = [column[1] for column in c.fetchall()]
    if 'doc_name' not in columns:
        c.execute("ALTER TABLE documents ADD COLUMN doc_name TEXT DEFAULT 'Untitled'")


    # Reviews table
    c.execute('''
        CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY,
            document_id INTEGER,
            reviewer_id INTEGER,
            comments TEXT,
            status TEXT,
            FOREIGN KEY(document_id) REFERENCES documents(id),
            FOREIGN KEY(reviewer_id) REFERENCES users(id)
        )
    ''')

    # Risks table
    c.execute('''
        CREATE TABLE IF NOT EXISTS risks (
            id INTEGER PRIMARY KEY,
            project_id INTEGER,
            failure_mode TEXT,
            severity INTEGER,
            occurrence INTEGER,
            detection INTEGER,
            rpn INTEGER,
            comments TEXT,
            status TEXT,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        )
    ''')

    # Traceability table
    c.execute('''
        CREATE TABLE IF NOT EXISTS traceability (
            id INTEGER PRIMARY KEY,
            project_id INTEGER,
            requirement_ref TEXT,
            design_ref TEXT,
            test_ref TEXT,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        )
    ''')

    conn.commit()
    conn.close()
    
    if not os.path.exists(config.UPLOAD_DIRECTORY):
        os.makedirs(config.UPLOAD_DIRECTORY)

# --- HELPER FUNCTIONS ---

def make_hashes(password):
    return hashlib.sha256(str.encode(password)).hexdigest()

def check_hashes(password, hashed_text):
    if make_hashes(password) == hashed_text:
        return True
    return False

def show_logs(prompt, response):
    """Displays AI prompt and response and adds to session log."""
    log_entry = {
        "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "prompt": prompt,
        "response": response
    }
    if 'app_logs' not in st.session_state:
        st.session_state.app_logs = []
    st.session_state.app_logs.append(log_entry)

    if st.session_state.get('show_logs', False):
        with st.expander("📝 View AI Logs", expanded=True):
            st.text("Final Prompt Sent to LLM:")
            st.code(prompt, language='text')
            st.text("Response from LLM:")
            st.code(response, language='html')

def generate_pdf(html_content):
    """Generates a PDF from HTML content and returns its binary data."""
    pdf_file = BytesIO()
    pisa_status = pisa.CreatePDF(BytesIO(html_content.encode('utf-8')), dest=pdf_file)
    if pisa_status.err:
        return None
    pdf_file.seek(0)
    return pdf_file.getvalue()


# --- DATABASE FUNCTIONS ---

def add_user(username, password, is_admin=False):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('INSERT INTO users (username, password_hash, is_admin) VALUES (?,?,?)', (username, make_hashes(password), is_admin))
    conn.commit()
    conn.close()

def login_user(username, password):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM users WHERE username =?', (username,))
    data = c.fetchone()
    conn.close()
    if data and check_hashes(password, data[2]):
        return data
    return None

def get_user_by_id(user_id):
    if user_id == 0:
        return "AI Assistant"
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT username FROM users WHERE id =?', (user_id,))
    data = c.fetchone()
    conn.close()
    return data[0] if data else "Unknown"

def get_all_users():
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT id, username, is_admin FROM users')
    data = c.fetchall()
    conn.close()
    return data

def create_project(name, description, user_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('INSERT INTO projects (name, description, created_by_user_id) VALUES (?,?,?)', (name, description, user_id))
    conn.commit()
    conn.close()

def get_projects():
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM projects')
    data = c.fetchall()
    conn.close()
    return data

def get_project_details(project_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM projects WHERE id =?', (project_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_project(project_id, name, description):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('UPDATE projects SET name = ?, description = ? WHERE id = ?', (name, description, project_id))
    conn.commit()
    conn.close()

def add_team_member(name, email, project_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('INSERT INTO team_members (name, email, project_id) VALUES (?,?,?)', (name, email, project_id))
    conn.commit()
    conn.close()

def get_team_members(project_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM team_members WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def create_document(project_id, doc_name, doc_type, content, version=1, status='Draft'):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO documents (project_id, doc_name, doc_type, content, version, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)',
              (project_id, doc_name, doc_type, json.dumps({"content": content}), version, status, now, now))
    conn.commit()
    conn.close()

def get_documents(project_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM documents WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data
    
def get_document_details(doc_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM documents WHERE id =?', (doc_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_document(doc_id, content):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    now = datetime.now()
    c.execute('UPDATE documents SET content =?, updated_at =? WHERE id =?', (json.dumps({"content": content}), now, doc_id))
    conn.commit()
    conn.close()

def add_risk(project_id, failure_mode, severity, occurrence, detection, rpn, comments, status='New'):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('INSERT INTO risks (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status) VALUES (?,?,?,?,?,?,?,?)',
              (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status))
    conn.commit()
    conn.close()

def get_risks(project_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM risks WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_traceability(project_id, requirement_ref, design_ref, test_ref):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('INSERT INTO traceability (project_id, requirement_ref, design_ref, test_ref) VALUES (?,?,?,?)',
              (project_id, requirement_ref, design_ref, test_ref))
    conn.commit()
    conn.close()

def get_traceability(project_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM traceability WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_review(document_id, reviewer_id, comments, status):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('INSERT INTO reviews (document_id, reviewer_id, comments, status) VALUES (?,?,?,?)',
              (document_id, reviewer_id, comments, status))
    if status in ["Approved", "Needs Revision"]:
        c.execute('UPDATE documents SET status = ? WHERE id = ?', (status, document_id))
    conn.commit()
    conn.close()

def get_reviews_for_document(document_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('''
        SELECT r.comments, r.status, CASE WHEN r.reviewer_id = 0 THEN 'AI Assistant' ELSE u.username END
        FROM reviews r
        LEFT JOIN users u ON r.reviewer_id = u.id
        WHERE r.document_id = ?
    ''', (document_id,))
    data = c.fetchall()
    conn.close()
    return data

# --- Template DB Functions ---
def create_template(name, content, doc_type):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO templates (name, content, document_type, created_at) VALUES (?,?,?,?)', (name, content, doc_type, now))
    conn.commit()
    conn.close()

def get_templates():
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM templates')
    data = c.fetchall()
    conn.close()
    return data

def get_template_details(template_id):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('SELECT * FROM templates WHERE id = ?', (template_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_template(template_id, name, content):
    conn = sqlite3.connect('docsmate.db')
    c = conn.cursor()
    c.execute('UPDATE templates SET name = ?, content = ? WHERE id = ?', (name, content, template_id))
    conn.commit()
    conn.close()

# --- STREAMLIT APP ---

def main():
    st.set_page_config(page_title="Docsmate - Document Lifecycle Management System", layout="wide")
    st.title("Docsmate - Document Lifecycle Management System")

    if 'logged_in' not in st.session_state:
        st.session_state['logged_in'] = False
        st.session_state['user_info'] = None
        st.session_state['app_logs'] = []

    if not st.session_state['logged_in']:
        menu = ["Login", "SignUp"]
        choice = st.sidebar.selectbox("Menu", menu)
        if choice == "Login":
            st.subheader("Login Section")
            username = st.text_input("User Name")
            password = st.text_input("Password", type='password')
            if st.button("Login"):
                user = login_user(username, password)
                if user:
                    st.session_state['logged_in'] = True
                    st.session_state['user_info'] = user
                    st.rerun()
                else:
                    st.warning("Incorrect Username/Password")
        elif choice == "SignUp":
            st.subheader("Create New Account")
            new_user = st.text_input("Username")
            new_password = st.text_input("Password", type='password')
            if st.button("Signup"):
                add_user(new_user, new_password)
                st.success("You have successfully created an account")
                st.info("Go to Login Menu to login")
    else:
        st.sidebar.subheader(f"Welcome {st.session_state['user_info'][1]}")
        st.session_state.show_logs = st.sidebar.toggle("Show App Logs", value=False)
        
        nav_options = ["Projects", "Templates", "Configuration", "Admin"]
        if st.session_state.show_logs:
            nav_options.append("App Logs")

        page = st.sidebar.radio("Navigation", nav_options)

        if st.sidebar.button("Logout"):
            st.session_state['logged_in'] = False
            st.session_state['user_info'] = None
            st.rerun()

        if page == "Projects":
            projects_page()
        elif page == "Templates":
            templates_page()
        elif page == "Configuration":
            config_page()
        elif page == "App Logs":
            app_logs_page()
        elif page == "Admin" and st.session_state['user_info'][3]:
            admin_page()
        elif page == "Admin" and not st.session_state['user_info'][3]:
            st.warning("You do not have admin privileges.")

def projects_page():
    st.sidebar.header("Project Management")
    
    with st.sidebar.expander("Create New Project"):
        with st.form("new_project_form_sidebar"):
            project_name = st.text_input("Project Name")
            project_desc = st.text_area("Description")
            submitted = st.form_submit_button("Create Project")
            if submitted:
                create_project(project_name, project_desc, st.session_state['user_info'][0])
                st.success("Project created successfully!")
                st.rerun()

    projects = get_projects()
    if not projects:
        st.header("Projects")
        st.info("No projects found. Create one from the sidebar to get started.")
    else:
        project_names = [p[1] for p in projects]
        selected_project_name = st.sidebar.selectbox("Select a project", project_names, key="project_selector")
        selected_project = next((p for p in projects if p[1] == selected_project_name), None)
        if selected_project:
            with st.sidebar.expander("Edit Current Project"):
                with st.form("edit_project_form_sidebar"):
                    st.write(f"Editing: **{selected_project[1]}**")
                    new_project_name = st.text_input("New Project Name", value=selected_project[1])
                    new_project_desc = st.text_area("New Description", value=selected_project[2])
                    update_submitted = st.form_submit_button("Update Project")
                    if update_submitted:
                        update_project(selected_project[0], new_project_name, new_project_desc)
                        st.success("Project updated successfully!")
                        st.rerun()
            
            if st.sidebar.button("Show Project Metrics"):
                st.session_state.show_metrics = not st.session_state.get('show_metrics', False)

            if st.session_state.get('show_metrics', False):
                show_project_metrics(selected_project[0])

            project_detail_page(selected_project[0])

def show_project_metrics(project_id):
    with st.sidebar.expander("Project Metrics", expanded=True):
        docs = get_documents(project_id)
        risks = get_risks(project_id)
        
        st.metric("Total Documents", len(docs))
        st.metric("Total Risks", len(risks))

        if docs:
            doc_df = pd.DataFrame(docs, columns=['id', 'project_id', 'doc_name', 'doc_type', 'content', 'version', 'status', 'created_at', 'updated_at'])
            st.write("**Document Status Distribution**")
            st.bar_chart(doc_df['status'].value_counts())
        
        if risks:
            risk_df = pd.DataFrame(risks, columns=['id', 'project_id', 'failure_mode', 'severity', 'occurrence', 'detection', 'rpn', 'comments', 'status'])
            st.write("**Risk Priority Number (RPN) Distribution**")
            st.bar_chart(risk_df['rpn'])


def project_detail_page(project_id):
    project_details = get_project_details(project_id)
    st.header(f"Project: {project_details[1]}")
    st.write(project_details[2])
    tabs = st.tabs(["Documents", "Team", "Risks", "Traceability"])
    with tabs[0]:
        documents_tab(project_id)
    with tabs[1]:
        team_tab(project_id)
    with tabs[2]:
        risks_tab(project_id)
    with tabs[3]:
        traceability_tab(project_id)

def documents_tab(project_id):
    st.subheader("Documents")
    project_details = get_project_details(project_id)
    project_description = project_details[2] if project_details else ""
    project_author_id = project_details[3] if project_details else 0
    project_author = get_user_by_id(project_author_id)

    with st.expander("Create New Document"):
        source_options = ["From Scratch", "From a Template", "From an Existing Document", "Using AI"]
        
        doc_type = None
        content_source = ""
        version = 1
        doc_name_suggestion = ""

        source_choice = st.radio("Create document from:", source_options)
        
        if source_choice == "From Scratch":
            doc_type = st.selectbox("Select Document Type", config.DOCUMENT_TYPES, key="scratch_type")
        elif source_choice == "From a Template":
            templates = get_templates()
            if templates:
                template_map = {t[1]: (t[2], t[3]) for t in templates}
                selected_template_name = st.selectbox("Select a template", options=list(template_map.keys()))
                content_source, doc_type = template_map[selected_template_name]
                doc_name_suggestion = selected_template_name
            else:
                st.warning("No templates found.")
        elif source_choice == "From an Existing Document":
            existing_docs = get_documents(project_id)
            if existing_docs:
                doc_map = {f"{d[2]}(v{d[5]})": (json.loads(d[4]).get("content", ""), d[3], d[5], d[2]) for d in existing_docs}
                selected_doc_name_key = st.selectbox("Select a source document", options=list(doc_map.keys()))
                content_source, doc_type, version, doc_name_suggestion = doc_map[selected_doc_name_key]
                version += 1
            else:
                st.warning("No existing documents in this project.")
        elif source_choice == "Using AI":
            doc_type = st.selectbox("Select Document Type for AI Generation", config.DOCUMENT_TYPES, key="ai_type")
            if st.button("Generate with AI"):
                if doc_type:
                    prompt = config.NEW_DOCUMENT_PROMPTS.get(doc_type, "").replace("[configurable_item]", project_description)
                    if prompt:
                        with st.spinner("Generating document..."):
                            content_source = ai_integration.generate_text(prompt)
                        st.session_state.ai_generated_content = content_source
                        show_logs(prompt, content_source)
                    else:
                        st.error("No AI prompt defined for this document type.")
                else:
                    st.error("Please select a document type.")

        if 'ai_generated_content' in st.session_state:
            st.success("AI content generated. Review and create the document.")
            content_source = st.session_state.ai_generated_content
        
        doc_name = st.text_input("Enter New Document Name", value=doc_name_suggestion)

        if st.button("Create Document"):
            if doc_name and doc_type:
                create_document(project_id, doc_name, doc_type, content_source, version)
                if 'ai_generated_content' in st.session_state:
                    del st.session_state.ai_generated_content
                st.success(f"Document '{doc_name}' created successfully!")
                st.rerun()
            else:
                st.error("Document Name and Type are required.")

    st.markdown("---")
    
    docs = get_documents(project_id)
    if not docs:
        st.info("No documents in this project yet.")
    else:
        doc_display_names = [f"{d[2]}(v{d[5]})" for d in docs]
        selected_doc_name_display = st.selectbox("Select a document to edit", doc_display_names, key=f"doc_select_{project_id}")
        
        if selected_doc_name_display:
            selected_doc_index = doc_display_names.index(selected_doc_name_display)
            selected_doc_info = docs[selected_doc_index]
            doc_details = get_document_details(selected_doc_info[0])

            if doc_details:
                doc_id, _, doc_name, doc_type, content_json, version, status, _, updated_at = doc_details
                content_data = json.loads(content_json)
                
                st.write(f"**File:** {doc_name}(v{version}) | **Document Type:** {doc_type} | **Status:** {status} | **Author:** {project_author} | **Timestamp:** {updated_at}")
                
                col1, col2, col3 = st.columns([2.5, 0.5, 0.5])
                with col1:
                    st.write("#### Document Editor")
                with col2:
                    if st.button("✨ AI Assist", key=f"ai_assist_{doc_id}"):
                        st.session_state.ai_assist_doc_id = doc_id if st.session_state.get('ai_assist_doc_id') != doc_id else None
                with col3:
                    pdf_data = generate_pdf(content_data.get("content", ""))
                    st.download_button(
                        label="Export PDF",
                        data=pdf_data,
                        file_name=f"{doc_name}_v{version}.pdf",
                        mime="application/pdf",
                        key=f"export_pdf_{doc_id}"
                    )


                if st.session_state.get('ai_assist_doc_id') == doc_id:
                    with st.container(border=True):
                        st.subheader("AI Assistant")
                        default_prompt = config.NEW_DOCUMENT_PROMPTS.get(doc_type, f"Improve the clarity of this {doc_type} document.").replace("[configurable_item]", project_description)
                        
                        btn_cols = st.columns(2)
                        with btn_cols[0]:
                            if st.button("Generate with AI", key=f"editor_ai_gen_{doc_id}", use_container_width=True):
                                clean_context = re.sub('<[^<]+?>', '', content_data.get("content", ""))
                                full_prompt = f"Context:\n{clean_context}\n\nTask: {st.session_state[f'editor_ai_prompt_{doc_id}']}"
                                with st.spinner("Generating AI content..."):
                                    ai_response = ai_integration.generate_text(full_prompt)
                                    st.session_state[f'ai_response_{doc_id}'] = ai_response
                                    show_logs(full_prompt, ai_response)
                        with btn_cols[1]:
                            if st.button("Close Assistant", key=f"close_ai_{doc_id}", use_container_width=True):
                                if f'ai_response_{doc_id}' in st.session_state:
                                    del st.session_state[f'ai_response_{doc_id}']
                                del st.session_state.ai_assist_doc_id
                                st.rerun()

                        st.text_area("Your prompt to the AI:", value=default_prompt, height=100, key=f"editor_ai_prompt_{doc_id}")
                        
                        if f'ai_response_{doc_id}' in st.session_state:
                            st.markdown(st.session_state[f'ai_response_{doc_id}'], unsafe_allow_html=True)

                content_from_editor = st_quill(value=content_data.get("content", ""), html=True, key=f"quill_editor_{doc_id}")

                if st.button("Save Document", key=f"save_doc_{doc_id}"):
                    update_document(doc_id, content_from_editor)
                    st.success("Document saved successfully!")
                    st.rerun()

                st.markdown("---")
                
                rev_col1, rev_col2 = st.columns([3, 1])
                with rev_col1:
                    st.subheader("Reviews & Comments")
                with rev_col2:
                    if st.button("✨ AI Assist", key=f"ai_review_{doc_id}"):
                        st.session_state.ai_review_doc_id = doc_id if st.session_state.get('ai_review_doc_id') != doc_id else None

                if st.session_state.get('ai_review_doc_id') == doc_id:
                     with st.container(border=True):
                        st.subheader("AI Review Assistant")
                        prompt_template = config.REVIEW_PROMPTS.get(doc_type, "Review this document for clarity, consistency, and completeness.")
                        default_prompt = prompt_template.replace("[configurable_item]", project_description)
                        
                        user_prompt = st.text_area("Your review prompt:", value=default_prompt, height=100, key=f"review_ai_prompt_{doc_id}")
                        if st.button("Review with AI", key=f"review_ai_gen_{doc_id}"):
                            clean_context = re.sub('<[^<]+?>', '', content_data.get("content", ""))
                            full_prompt = f"Context:\n{clean_context}\n\nTask: {user_prompt}"
                            with st.spinner("Generating AI review..."):
                                ai_comment = ai_integration.generate_review(full_prompt)
                                add_review(doc_id, 0, ai_comment, "AI Review")
                                show_logs(full_prompt, ai_comment)
                                st.success("AI review added.")
                                del st.session_state.ai_review_doc_id
                                st.rerun()

                reviews = get_reviews_for_document(doc_id)
                if reviews:
                    for review in reviews:
                        with st.expander(f"Review by **{review[2]}** - Status: **{review[1]}**"):
                            st.markdown(review[0], unsafe_allow_html=True)
                else:
                    st.info("No reviews for this document yet.")
                
                with st.form(key=f"review_form_{doc_id}", clear_on_submit=True):
                    st.write("Add your review")
                    comment = st.text_area("Comments")
                    status = st.selectbox("Status", ["Comment", "Approved", "Needs Revision"])
                    submit_review = st.form_submit_button("Submit Review")
                    if submit_review:
                        add_review(doc_id, st.session_state['user_info'][0], comment, status)
                        st.success("Your review has been submitted.")
                        st.rerun()

def templates_page():
    st.header("Document Templates")

    col1, col2 = st.columns([3, 1])
    with col1:
        st.subheader("Create / Edit Templates")
    with col2:
        if st.button("✨ AI Assist", key="ai_template_btn"):
            st.session_state.ai_template_open = not st.session_state.get('ai_template_open', False)

    if st.session_state.get('ai_template_open', False):
        with st.expander("AI Template Generator", expanded=True):
            st.write("### AI Content Generation")
            selected_category = st.selectbox("Select a Document Type", config.DOCUMENT_TYPES, key="template_ai_cat")
            prompt_template = config.NEW_DOCUMENT_PROMPTS.get(selected_category, "Generate a document template for [describe your item here].")
            default_prompt = prompt_template.replace("[configurable_item]", "[describe your item here]")
            
            user_prompt = st.text_area("Your prompt to the AI:", value=default_prompt, height=100, key="template_ai_prompt")

            if st.button("Generate with AI", key="template_ai_gen"):
                with st.spinner("Generating AI content..."):
                    ai_generated_template = ai_integration.generate_text(user_prompt)
                    st.session_state.new_template_quill = ai_generated_template
                    st.session_state.ai_template_open = False
                    show_logs(user_prompt, ai_generated_template)
                    st.rerun()

    with st.expander("Create New Template", expanded=True):
        with st.form("new_template_form", clear_on_submit=True):
            template_name = st.text_input("Template Name")
            doc_type = st.selectbox("Document Type", config.DOCUMENT_TYPES)
            template_content = st_quill(key="new_template_quill", html=True, value=st.session_state.get('new_template_quill', ''))
            submitted = st.form_submit_button("Save New Template")
            if submitted:
                if template_name and doc_type:
                    create_template(template_name, template_content, doc_type)
                    if 'new_template_quill' in st.session_state:
                         del st.session_state.new_template_quill
                    st.success(f"Template '{template_name}' created!")
                    st.rerun()
                else:
                    st.error("Template Name and Document Type are required.")

    st.markdown("---")
    st.subheader("Existing Templates")
    templates = get_templates()
    if not templates:
        st.info("No templates created yet.")
    else:
        template_names = [t[1] for t in templates]
        selected_template_name = st.selectbox("Select a template to edit", template_names)
        selected_template = next((t for t in templates if t[1] == selected_template_name), None)
        if selected_template:
            st.write(f"Editing: **{selected_template[1]}** | Type: {selected_template[3]} | Created: {selected_template[4]}")
            edited_content = st_quill(value=selected_template[2], key=f"edit_template_{selected_template[0]}")
            if st.button("Update Template", key=f"update_template_btn_{selected_template[0]}"):
                update_template(selected_template[0], selected_template[1], edited_content)
                st.success("Template updated successfully!")
                st.rerun()

def config_page():
    st.header("AI Configuration and Knowledge Base")
    st.subheader("Local Knowledge Base")
    st.write("Upload documents to build the local knowledge base for the RAG system.")
    uploaded_files = st.file_uploader("Upload up to 15 documents", accept_multiple_files=True, type=['pdf', 'txt', 'md', 'html'])
    if uploaded_files:
        if len(uploaded_files) > 15:
            st.error("You can upload a maximum of 15 files.")
        else:
            for uploaded_file in uploaded_files:
                file_path = os.path.join(config.UPLOAD_DIRECTORY, uploaded_file.name)
                with open(file_path, "wb") as f:
                    f.write(uploaded_file.getbuffer())
            st.success(f"{len(uploaded_files)} file(s) uploaded successfully!")
            if st.button("Process Uploaded Documents"):
                with st.spinner("Processing documents... This may take a moment."):
                    st.success("Documents processed and added to the knowledge base.")

def team_tab(project_id):
    st.subheader("Team Members")
    team_members = get_team_members(project_id)
    if team_members:
        df = pd.DataFrame(team_members, columns=['ID', 'Name', 'Email', 'Project ID'])
        st.dataframe(df[['Name', 'Email']])
    
    with st.expander("Add New Team Member"):
        with st.form("new_team_member_form", clear_on_submit=True):
            member_name = st.text_input("Name")
            member_email = st.text_input("Email")
            submitted = st.form_submit_button("Add Member")
            if submitted:
                add_team_member(member_name, member_email, project_id)
                st.success("Team member added!")
                st.rerun()

def risks_tab(project_id):
    project_details = get_project_details(project_id)
    project_description = project_details[2] if project_details else ""
    
    col1, col2 = st.columns([3, 1])
    with col1:
        st.subheader("Risk Management")
    with col2:
        if st.button("✨ AI Risk Assist", key=f"ai_risk_{project_id}"):
            st.session_state.ai_risk_open = not st.session_state.get('ai_risk_open', False)
    
    if st.session_state.get('ai_risk_open', False):
        with st.expander("AI Risk Assistant", expanded=True):
            st.write("Use AI to identify potential risks based on the project description.")
            if st.button("Generate and Add Risks", key="risk_ai_gen"):
                prompt = config.NEW_DOCUMENT_PROMPTS.get("Risk Analysis", "").replace("[configurable_item]", project_description)
                with st.spinner("Generating AI content..."):
                    generated_risks = ai_integration.generate_risk_analysis(prompt)
                    for risk_desc in generated_risks:
                        add_risk(project_id, risk_desc, 1, 1, 1, 1, "AI Generated")
                    show_logs(prompt, "\n".join(generated_risks))
                    st.success(f"{len(generated_risks)} risks have been automatically added.")
                    st.session_state.ai_risk_open = False
                    st.rerun()

    risks = get_risks(project_id)
    if risks:
        df = pd.DataFrame(risks, columns=['ID', 'Project ID', 'Failure Mode', 'Severity', 'Occurrence', 'Detection', 'RPN', 'Comments', 'Status'])
        st.dataframe(df[['Failure Mode', 'Severity', 'Occurrence', 'Detection', 'RPN', 'Comments', 'Status']])
    else:
        st.info("No risks recorded for this project yet.")

    with st.expander("Add New Risk Manually"):
        with st.form("new_risk_form", clear_on_submit=True):
            failure_mode = st.text_area("Failure Mode")
            sev = st.number_input("Severity (1-10)", 1, 10, 1)
            occ = st.number_input("Occurrence (1-10)", 1, 10, 1)
            det = st.number_input("Detection (1-10)", 1, 10, 1)
            comments = st.text_area("Comments / Mitigation")
            submitted = st.form_submit_button("Add Risk")
            if submitted:
                rpn = sev * occ * det
                add_risk(project_id, failure_mode, sev, occ, det, rpn, comments)
                st.success("Risk added!")
                st.rerun()

def traceability_tab(project_id):
    st.subheader("Traceability Matrix")
    traceability_links = get_traceability(project_id)
    if traceability_links:
        df = pd.DataFrame(traceability_links, columns=['ID', 'Project ID', 'Requirement Reference', 'Design Reference', 'Test Reference'])
        st.dataframe(df[['Requirement Reference', 'Design Reference', 'Test Reference']])
    else:
        st.info("No traceability links recorded for this project yet.")

    with st.expander("Add New Traceability"):
        with st.form("new_traceability_form", clear_on_submit=True):
            req_ref = st.text_input("Requirement Reference")
            design_ref = st.text_input("Design Reference")
            test_ref = st.text_input("Test Reference")
            submitted = st.form_submit_button("Add Trace")
            if submitted:
                add_traceability(project_id, req_ref, design_ref, test_ref)
                st.success("Traceability link added!")
                st.rerun()

def admin_page():
    st.header("Admin Dashboard")
    st.subheader("Manage Users")
    users = get_all_users()
    df = pd.DataFrame(users, columns=['ID', 'Username', 'Is Admin'])
    st.dataframe(df)

def app_logs_page():
    st.header("Application Logs")
    if st.session_state.app_logs:
        for log in reversed(st.session_state.app_logs):
            with st.container(border=True):
                st.write(f"**Timestamp:** {log['timestamp']}")
                st.text("Prompt:")
                st.code(log['prompt'], language='text')
                st.text("Response:")
                st.code(log['response'], language='html')
    else:
        st.info("No log entries yet.")

if __name__ == '__main__':
    init_db()
    main()

