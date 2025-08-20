# app.py
# Main application file for docsmate.

import streamlit as st
import sqlite3
import hashlib
import json
import pandas as pd
from datetime import datetime
import os
import config  # Import the configuration file
import ai_integration # Import the AI integration module
import re
from xhtml2pdf import pisa
from io import BytesIO
import zipfile
import matplotlib.pyplot as plt
import difflib
from streamlit_quill import st_quill


# --- DATABASE SETUP ---

def init_db():
    """Initializes the SQLite database and creates tables if they don't exist."""
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()

    # User table
    c.execute('''
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            username TEXT UNIQUE,
            email TEXT UNIQUE,
            password_hash TEXT,
            is_admin BOOLEAN
        )
    ''')
    # Add email column if it doesn't exist
    c.execute("PRAGMA table_info(users)")
    columns = [column[1] for column in c.fetchall()]
    if 'email' not in columns:
        c.execute("ALTER TABLE users ADD COLUMN email TEXT UNIQUE")

    # Add aiuser if not exists
    c.execute("SELECT * FROM users WHERE username='aiuser'")
    if not c.fetchone():
        add_user('aiuser', 'ai_password', 'aiuser@docsmate.com', is_admin=False)


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
            user_id INTEGER,
            project_id INTEGER,
            FOREIGN KEY(project_id) REFERENCES projects(id),
            FOREIGN KEY(user_id) REFERENCES users(id)
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

    # Hazard Traceability Matrix table
    c.execute('''
        CREATE TABLE IF NOT EXISTS hazard_traceability (
            id INTEGER PRIMARY KEY,
            project_id INTEGER,
            hazard TEXT,
            cause TEXT,
            effect TEXT,
            risk_control_measure TEXT,
            verification TEXT,
            severity INTEGER,
            occurrence INTEGER,
            detection INTEGER,
            mitigation_notes TEXT,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        )
    ''')
    # Check and add new columns for backward compatibility
    c.execute("PRAGMA table_info(hazard_traceability)")
    columns = [column[1] for column in c.fetchall()]
    if 'severity' not in columns:
        c.execute("ALTER TABLE hazard_traceability ADD COLUMN severity INTEGER")
    if 'occurrence' not in columns:
        c.execute("ALTER TABLE hazard_traceability ADD COLUMN occurrence INTEGER")
    if 'detection' not in columns:
        c.execute("ALTER TABLE hazard_traceability ADD COLUMN detection INTEGER")
    if 'mitigation_notes' not in columns:
        c.execute("ALTER TABLE hazard_traceability ADD COLUMN mitigation_notes TEXT")
    
    # Revision History table
    c.execute('''
        CREATE TABLE IF NOT EXISTS revision_history (
            id INTEGER PRIMARY KEY,
            doc_id INTEGER,
            status TEXT,
            author_id INTEGER,
            timestamp DATETIME,
            comments TEXT,
            FOREIGN KEY(doc_id) REFERENCES documents(id),
            FOREIGN KEY(author_id) REFERENCES users(id)
        )
    ''')
    
    # ASIL Tables
    c.execute('''CREATE TABLE IF NOT EXISTS asil_master (id INTEGER PRIMARY KEY, project_id INTEGER, hazard_description TEXT, severity TEXT, exposure TEXT, controllability TEXT, asil_rating TEXT)''')
    
    # SIL Tables
    c.execute('''CREATE TABLE IF NOT EXISTS sil_master (id INTEGER PRIMARY KEY, project_id INTEGER, hazard_description TEXT, consequence TEXT, exposure TEXT, avoidance TEXT, probability TEXT, sil_rating TEXT)''')


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
    """Generates a PDF from HTML content and returns its binary data."""
    pdf_file = BytesIO()
    pisa_status = pisa.CreatePDF(BytesIO(html_content.encode('utf-8')), dest=pdf_file)
    if pisa_status.err:
        return None
    pdf_file.seek(0)
    return pdf_file.getvalue()


# --- DATABASE FUNCTIONS ---

def add_user(username, password, email, is_admin=False):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO users (username, password_hash, email, is_admin) VALUES (?,?,?,?)', (username, make_hashes(password), email, is_admin))
    conn.commit()
    conn.close()

def login_user(email, password):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM users WHERE email =?', (email,))
    data = c.fetchone()
    conn.close()
    if data and check_hashes(password, data[3]):
        return data
    return None

def get_user_by_id(user_id):
    if user_id == 0:
        return "AI Assistant"
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT username FROM users WHERE id =?', (user_id,))
    data = c.fetchone()
    conn.close()
    return data[0] if data else "Unknown"

def get_all_users():
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT id, username, is_admin FROM users')
    data = c.fetchall()
    conn.close()
    return data

def create_project(name, description, user_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO projects (name, description, created_by_user_id) VALUES (?,?,?)', (name, description, user_id))
    conn.commit()
    conn.close()

def get_projects():
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM projects')
    data = c.fetchall()
    conn.close()
    return data

def get_project_details(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM projects WHERE id =?', (project_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_project(project_id, name, description):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('UPDATE projects SET name = ?, description = ? WHERE id = ?', (name, description, project_id))
    conn.commit()
    conn.close()

def add_team_member(user_id, project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO team_members (user_id, project_id) VALUES (?,?)', (user_id, project_id))
    conn.commit()
    conn.close()

def get_team_members(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('''SELECT u.id, u.username, u.email FROM users u JOIN team_members tm ON u.id = tm.user_id WHERE tm.project_id = ?''', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def create_document(project_id, item_name, doc_type, content, version=1, status='Draft'):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO documents (project_id, doc_name, doc_type, content, version, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)',
              (project_id, item_name, doc_type, json.dumps({"content": content}), version, status, now, now))
    conn.commit()
    conn.close()

def get_documents(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM documents WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data
    
def get_document_details(doc_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM documents WHERE id =?', (doc_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_document(doc_id, content, status, comments, author_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('UPDATE documents SET content =?, status = ?, updated_at =? WHERE id =?', (json.dumps({"content": content}), status, now, doc_id))
    if status != "Draft":
        add_revision_history(doc_id, status, author_id, comments, c)
    conn.commit()
    conn.close()

def add_risk(project_id, failure_mode, severity, occurrence, detection, rpn, comments, status='New'):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO risks (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status) VALUES (?,?,?,?,?,?,?,?)',
              (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status))
    conn.commit()
    conn.close()

def get_risks(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM risks WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_traceability(project_id, requirement_ref, design_ref, test_ref):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO traceability (project_id, requirement_ref, design_ref, test_ref) VALUES (?,?,?,?)',
              (project_id, requirement_ref, design_ref, test_ref))
    conn.commit()
    conn.close()

def get_traceability(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM traceability WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_review(document_id, reviewer_id, comments, status):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO reviews (document_id, reviewer_id, comments, status) VALUES (?,?,?,?)',
              (document_id, reviewer_id, comments, status))
    if status in ["Approved", "Needs Revision"]:
        c.execute('UPDATE documents SET status = ? WHERE id = ?', (status, document_id))
        add_revision_history(document_id, status, reviewer_id, comments, c)
    conn.commit()
    conn.close()

def get_reviews_for_document(document_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
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
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO templates (name, content, document_type, created_at) VALUES (?,?,?,?)', (name, content, doc_type, now))
    conn.commit()
    conn.close()

def get_templates():
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM templates')
    data = c.fetchall()
    conn.close()
    return data

def get_template_details(template_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM templates WHERE id = ?', (template_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_template(template_id, name, content):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('UPDATE templates SET name = ?, content = ? WHERE id = ?', (name, content, template_id))
    conn.commit()
    conn.close()

def add_hazard_traceability(project_id, hazard, cause, effect, risk_control_measure, verification, severity, occurrence, detection, mitigation_notes):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO hazard_traceability (project_id, hazard, cause, effect, risk_control_measure, verification, severity, occurrence, detection, mitigation_notes) VALUES (?,?,?,?,?,?,?,?,?,?)',
              (project_id, hazard, cause, effect, risk_control_measure, verification, severity, occurrence, detection, mitigation_notes))
    conn.commit()
    conn.close()

def get_hazard_traceability(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM hazard_traceability WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_revision_history(doc_id, status, author_id, comments, db_cursor=None):
    conn = None
    if not db_cursor:
        conn = sqlite3.connect('docsmate.db', timeout=15)
        db_cursor = conn.cursor()
    
    now = datetime.now()
    db_cursor.execute('INSERT INTO revision_history (doc_id, status, author_id, timestamp, comments) VALUES (?,?,?,?,?)',
              (doc_id, status, author_id, now, comments))
    
    if conn:
        conn.commit()
        conn.close()

def get_revision_history(doc_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM revision_history WHERE doc_id =?', (doc_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_asil_entry(project_id, hazard_desc, severity, exposure, controllability, asil_rating):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO asil_master (project_id, hazard_description, severity, exposure, controllability, asil_rating) VALUES (?,?,?,?,?,?)',
              (project_id, hazard_desc, severity, exposure, controllability, asil_rating))
    conn.commit()
    conn.close()

def get_asil_entries(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM asil_master WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_sil_entry(project_id, hazard_desc, consequence, exposure, avoidance, probability, sil_rating):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO sil_master (project_id, hazard_description, consequence, exposure, avoidance, probability, sil_rating) VALUES (?,?,?,?,?,?,?)',
              (project_id, hazard_desc, consequence, exposure, avoidance, probability, sil_rating))
    conn.commit()
    conn.close()

def get_sil_entries(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM sil_master WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

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
            email = st.text_input("Email")
            password = st.text_input("Password", type='password')
            if st.button("Login"):
                user = login_user(email, password)
                if user:
                    st.session_state['logged_in'] = True
                    st.session_state['user_info'] = user
                    st.rerun()
                else:
                    st.warning("Incorrect Email/Password")
        elif choice == "SignUp":
            st.subheader("Create New Account")
            new_user = st.text_input("Username")
            new_email = st.text_input("Email")
            new_password = st.text_input("Password", type='password')
            if st.button("Signup"):
                add_user(new_user, new_password, new_email)
                st.success("You have successfully created an account")
                st.info("Go to Login Menu to login")
    else:
        st.sidebar.subheader(f"Welcome {st.session_state['user_info'][1]}")
        st.session_state.show_logs = st.sidebar.toggle("Show App Logs", value=False)
        
        nav_options = ["Projects", "Templates", "Configuration", "Admin", "Help"]
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
        elif page == "Admin" and st.session_state['user_info'][4]:
            admin_page()
        elif page == "Help":
            help_page()
        elif page == "Admin" and not st.session_state['user_info'][4]:
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
            
            project_detail_page(selected_project[0])

def project_detail_page(project_id):
    project_details = get_project_details(project_id)
    st.header(f"Project: {project_details[1]}")
    st.write(project_details[2])
    tabs = st.tabs(["Team", "Documents", "Code", "Reviews", "Hazards", "Traceability", "Artifacts", "Metrics", "AI Audit"])
    with tabs[0]:
        team_tab(project_id)
    with tabs[1]:
        documents_tab(project_id)
    with tabs[2]:
        code_tab(project_id)
    with tabs[3]:
        reviews_tab(project_id)
    with tabs[4]:
        hazard_traceability_tab(project_id)
    with tabs[5]:
        traceability_tab(project_id)
    with tabs[6]:
        artifacts_tab(project_id)
    with tabs[7]:
        metrics_tab(project_id)
    with tabs[8]:
        ai_audit_tab(project_id)


def documents_tab(project_id):
    st.subheader("Documents")
    project_details = get_project_details(project_id)
    project_description = project_details[2] if project_details else ""
    project_author_id = project_details[3] if project_details else 0
    project_author = get_user_by_id(project_author_id)

    with st.expander("Create Document"):
        source_options = ["From Scratch", "From a Template", "From an Existing Document"]
        
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
        

        if 'ai_generated_content' in st.session_state:
            st.success("AI content generated. Review and create the document.")
            content_source = st.session_state.ai_generated_content
        
        item_name = st.text_input("Enter Document Name", value=doc_name_suggestion)

        if st.button("Create Document"):
            if item_name and doc_type:
                create_document(project_id, item_name, doc_type, content_source, version)
                if 'ai_generated_content' in st.session_state:
                    del st.session_state.ai_generated_content
                st.success(f"Document '{item_name}' created successfully!")
                st.rerun()
            else:
                st.error("Document Name and Type are required.")

    st.markdown("---")
    
    docs = get_documents(project_id)
    if not docs:
        st.info("No review items in this project yet.")
    else:
        doc_display_names = [f"{d[2]}(v{d[5]})" for d in docs]
        selected_doc_name_display = st.selectbox("Select a review item to edit", doc_display_names, key=f"doc_select_{project_id}")
        
        if selected_doc_name_display:
            selected_doc_index = doc_display_names.index(selected_doc_name_display)
            selected_doc_info = docs[selected_doc_index]
            doc_details = get_document_details(selected_doc_info[0])

            if doc_details:
                doc_id, _, doc_name, doc_type, content_json, version, status, _, updated_at = doc_details
                content_data = json.loads(content_json)
                
                col1, col2, col3 = st.columns([2.5, 0.5, 0.5])
                with col1:
                    st.write("#### Document Editor")
                with col2:
                    if st.button("✨ AI Assist", key=f"ai_assist_{doc_id}"):
                        st.session_state.ai_assist_doc_id = doc_id if st.session_state.get('ai_assist_doc_id') != doc_id else None
                with col3:
                    pdf_data = generate_pdf(content_data.get("content", ""))
                    if pdf_data:
                        st.download_button(
                            label="Export PDF",
                            data=pdf_data,
                            file_name=f"{doc_name}_v{version}.pdf",
                            mime="application/pdf",
                            key=f"export_pdf_{doc_id}"
                        )
                    else:
                        st.warning("Couldn't generate PDF for this document.")


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
                            st.text_area("Response from the AI:", value=st.session_state[f'ai_response_{doc_id}'], height=300, key=f"editor_ai_response_{doc_id}")

                content_from_editor = st_quill(value=content_data.get("content", ""), html=True, key=f"quill_editor_{doc_id}")
                
                st.write(f"**File:** {doc_name}(v{version}) | **Status:** {status}")
                
                next_state = st.selectbox("Select Next State", ["Draft", "Request Review"])
                
                if next_state == "Request Review":
                    author_comment = st.text_input("Author comment to Reviewer")
                    
                    users = get_all_users()
                    user_map = {u[1]: u[0] for u in users}
                    reviewers = st.multiselect("Select Reviewers", list(user_map.keys()))
                else:
                    author_comment = ""
                    reviewers = []

                if st.button("Save", key=f"save_doc_{doc_id}"):
                    if next_state == "Request Review":
                        if not reviewers:
                            st.error("Please select at least one reviewer.")
                        elif not author_comment:
                            st.error("Please provide a comment for the reviewer.")
                        else:
                            update_document(doc_id, content_from_editor, "Review Request", author_comment, st.session_state['user_info'][0])
                            for reviewer_name in reviewers:
                                reviewer_id = user_map[reviewer_name]
                                add_review(doc_id, reviewer_id, "", "Pending")
                            st.success("Document saved and review requested!")
                            st.rerun()
                    else:
                        update_document(doc_id, content_from_editor, "Draft", author_comment, st.session_state['user_info'][0])
                        st.success("Document saved as draft!")
                        st.rerun()

                st.markdown("---")
                
                st.subheader("Revision History")
                history = get_revision_history(doc_id)
                if history:
                    df = pd.DataFrame(history, columns=['ID', 'Doc ID', 'Status', 'Author ID', 'Timestamp', 'Comments'])
                    df['Author'] = df['Author ID'].apply(get_user_by_id)
                    st.dataframe(df[['Status', 'Author', 'Timestamp', 'Comments']])
                else:
                    st.info("No revision history for this review item yet.")

def colorize_diff_to_html(diff_text):
    """Converts diff text to colorized HTML."""
    import html
    html_lines = []
    for line in diff_text.splitlines():
        escaped_line = html.escape(line)
        if line.startswith('+') and not line.startswith('+++'):
            html_lines.append(f'<span style="color: #28a745; background-color: #e6ffed;">{escaped_line}</span>')
        elif line.startswith('-') and not line.startswith('---'):
            html_lines.append(f'<span style="color: #dc3545; background-color: #ffeef0;">{escaped_line}</span>')
        elif line.startswith('@@'):
            html_lines.append(f'<span style="color: #17a2b8;">{escaped_line}</span>')
        elif line.startswith('diff --git'):
            html_lines.append(f'<span style="font-weight: bold;">{escaped_line}</span>')
        else:
            html_lines.append(f'<span>{escaped_line}</span>')
    return '<pre style="background-color: #f6f8fa; border: 1px solid #ced4da; border-radius: 5px; padding: 10px; font-family: monospace;"><code>' + '\n'.join(html_lines) + '</code></pre>'

def code_tab(project_id):
    st.subheader("Submit Code for Review")

    diff_input = ""
    uploaded_file = st.file_uploader("Upload a Diff File")
    if uploaded_file:
        diff_input = uploaded_file.getvalue().decode("utf-8")
    
    diff_text_area = st.text_area("Paste Git Diff Output Here", value=diff_input if diff_input else "", height=300)

    final_diff = diff_text_area or diff_input

    if final_diff:
        with st.form("create_code_review_form"):
            item_name = st.text_input("Code Review Item Name")
            submitted = st.form_submit_button("Create Code Review Item")
            if submitted:
                if item_name:
                    content = {"raw_diff": final_diff}
                    create_document(project_id, item_name, "Code Review", content, status="Review Request")
                    st.success(f"Code Review Item '{item_name}' created successfully!")
                    st.rerun()
                else:
                    st.error("Please provide a name for the review item.")

    st.markdown("---")
    st.subheader("View Existing Code Reviews")
    docs = get_documents(project_id)
    code_reviews = [d for d in docs if d[3] == "Code Review"]
    if not code_reviews:
        st.info("No code review items in this project yet.")
    else:
        review_item_names = [d[2] for d in code_reviews]
        selected_item_name = st.selectbox("Select a code review item to view diff", review_item_names)
        if selected_item_name:
            selected_item = next((d for d in code_reviews if d[2] == selected_item_name), None)
            if selected_item:
                content_data = json.loads(selected_item[4])
                payload = content_data.get("content", {})
                if isinstance(payload, str):
                    try:
                        payload = json.loads(payload)
                    except Exception:
                        payload = {}
                
                raw_diff = payload.get("raw_diff")
                if raw_diff:
                    diff_html = colorize_diff_to_html(raw_diff)
                    st.components.v1.html(diff_html, height=600, scrolling=True)
                else:  # Fallback for old format
                    code1 = payload.get("code1", "")
                    code2 = payload.get("code2", "")
                    d = difflib.HtmlDiff()
                    diff_html = d.make_table(code1.splitlines(), code2.splitlines())
                    st.components.v1.html(diff_html, height=600, scrolling=True)

                # Add Your Review section
                st.subheader("Add Your Review")
                with st.form(key=f"code_review_form_{selected_item[0]}", clear_on_submit=True):
                    comment = st.text_area("Comments")
                    status = st.selectbox("Status", ["Comment", "Approved", "Needs Revision"])
                    submit_review = st.form_submit_button("Submit Review")
                    if submit_review:
                        add_review(selected_item[0], st.session_state['user_info'][0], comment, status)
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
        df = pd.DataFrame(team_members, columns=['ID', 'Username', 'Email'])
        st.dataframe(df[['Username', 'Email']])
    
    with st.expander("Add New Team Member"):
        with st.form("new_team_member_form", clear_on_submit=True):
            users = get_all_users()
            user_map = {u[1]: u[0] for u in users}
            selected_user = st.selectbox("Select User", list(user_map.keys()))
            submitted = st.form_submit_button("Add Member")
            if submitted:
                user_id = user_map[selected_user]
                add_team_member(user_id, project_id)
                st.success("Team member added!")
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

def hazard_traceability_tab(project_id):
    st.subheader("Hazards")
    hazard_assessment_type = st.selectbox("Select Hazard Assessment Type", ["Medical (IEC 62304)", "Automotive (ISO 26262)", "General (IEC 61508)"])

    if hazard_assessment_type == "Medical (IEC 62304)":
        hazard_links = get_hazard_traceability(project_id)
        if hazard_links:
            df = pd.DataFrame(hazard_links, columns=['ID', 'Project ID', 'Hazard/Failure', 'Cause', 'Effect', 'Control Measures', 'Verification Notes', 'Severity', 'Occurrence', 'Detection', 'Mitigation Notes'])
            st.data_editor(df[['Hazard/Failure', 'Cause', 'Effect', 'Control Measures', 'Verification Notes', 'Severity', 'Occurrence', 'Detection', 'Mitigation Notes']])
        else:
            st.info("No hazard traceability links recorded for this project yet.")

        with st.expander("Add New Hazard Traceability"):
            with st.form("new_hazard_traceability_form", clear_on_submit=True):
                hazard = st.text_input("Hazard/Failure")
                cause = st.text_input("Cause")
                effect = st.text_input("Effect")
                risk_control = st.text_input("Control Measures")
                verification = st.text_input("Verification Notes")
                sev = st.number_input("Severity (1-10)", 1, 10, 1)
                occ = st.number_input("Occurrence (1-10)", 1, 10, 1)
                det = st.number_input("Detection (1-10)", 1, 10, 1)
                mitigation_notes = st.text_area("Mitigation Notes")
                submitted = st.form_submit_button("Add Hazard")
                if submitted:
                    add_hazard_traceability(project_id, hazard, cause, effect, risk_control, verification, sev, occ, det, mitigation_notes)
                    st.success("Hazard traceability link added!")
                    st.rerun()
    
    elif hazard_assessment_type == "Automotive (ISO 26262)":
        st.subheader("ASIL Determination")
        asil_entries = get_asil_entries(project_id)
        if asil_entries:
            df = pd.DataFrame(asil_entries, columns=['ID', 'Project ID', 'Hazard Description', 'Severity', 'Exposure', 'Controllability', 'ASIL Rating'])
            st.dataframe(df[['Hazard Description', 'Severity', 'Exposure', 'Controllability', 'ASIL Rating']])
        else:
            st.info("No ASIL entries recorded for this project yet.")

        with st.expander("Add New ASIL Entry"):
            with st.form("new_asil_form", clear_on_submit=True):
                hazard_desc = st.text_input("Hazard Description")
                sev = st.selectbox("Severity", options=list(config.ASIL_SEVERITY.keys()))
                exp = st.selectbox("Exposure", options=list(config.ASIL_EXPOSURE.keys()))
                con = st.selectbox("Controllability", options=list(config.ASIL_CONTROLLABILITY.keys()))
                submitted = st.form_submit_button("Calculate and Add ASIL")
                if submitted:
                    asil_rating = config.ASIL_RATING_TABLE.get(config.ASIL_SEVERITY[sev] + config.ASIL_EXPOSURE[exp] + config.ASIL_CONTROLLABILITY[con], "QM")
                    add_asil_entry(project_id, hazard_desc, sev, exp, con, asil_rating)
                    st.success("ASIL entry added!")
                    st.rerun()

    elif hazard_assessment_type == "General (IEC 61508)":
        st.subheader("SIL Determination")
        sil_entries = get_sil_entries(project_id)
        if sil_entries:
            df = pd.DataFrame(sil_entries, columns=['ID', 'Project ID', 'Hazard Description', 'Consequence', 'Exposure', 'Avoidance', 'Probability', 'SIL Rating'])
            st.dataframe(df[['Hazard Description', 'Consequence', 'Exposure', 'Avoidance', 'Probability', 'SIL Rating']])
        else:
            st.info("No SIL entries recorded for this project yet.")

        with st.expander("Add New SIL Entry"):
            with st.form("new_sil_form", clear_on_submit=True):
                hazard_desc = st.text_input("Hazard Description")
                cons = st.selectbox("Consequence", options=list(config.SIL_CONSEQUENCE.keys()))
                exp = st.selectbox("Exposure", options=list(config.SIL_EXPOSURE.keys()))
                avo = st.selectbox("Avoidance", options=list(config.SIL_AVOIDANCE.keys()))
                prob = st.selectbox("Probability", options=list(config.SIL_PROBABILITY.keys()))
                submitted = st.form_submit_button("Calculate and Add SIL")
                if submitted:
                    sil_rating = config.SIL_RATING_TABLE.get(
                        (config.SIL_PROBABILITY[prob], config.SIL_CONSEQUENCE[cons], config.SIL_EXPOSURE[exp], config.SIL_AVOIDANCE[avo]),
                        "Invalid"
                    )
                    add_sil_entry(project_id, hazard_desc, cons, exp, avo, prob, sil_rating)
                    st.success("SIL entry added!")
                    st.rerun()

def artifacts_tab(project_id):
    st.subheader("Artifacts")
    
    docs = get_documents(project_id)
    approved_docs = [d for d in docs if d[6] == "Approved"]

    if not approved_docs:
        st.info("No approved documents yet.")
    else:
        df = pd.DataFrame(approved_docs, columns=['id', 'project_id', 'doc_name', 'doc_type', 'content', 'version', 'status', 'created_at', 'updated_at'])
        df['author'] = get_user_by_id(get_project_details(project_id)[3])
        st.dataframe(df[['doc_name', 'doc_type', 'version', 'author', 'updated_at']])

        if st.button("Download All"):
            zip_buffer = BytesIO()
            with zipfile.ZipFile(zip_buffer, "a", zipfile.ZIP_DEFLATED, False) as zip_file:
                added, skipped = 0, 0
                for index, row in df.iterrows():
                    pdf_data = generate_pdf(json.loads(row['content']).get("content", ""))
                    if pdf_data:
                        zip_file.writestr(f"{row['doc_name']}_v{row['version']}.pdf", pdf_data)
                        added += 1
                    else:
                        skipped += 1
            
            if skipped:
                st.warning(f"{skipped} document(s) could not be converted to PDF and were skipped.")
            st.download_button(
                label="Download All as ZIP",
                data=zip_buffer.getvalue(),
                file_name=f"{get_project_details(project_id)[1]}_Documents_{datetime.now().strftime('%Y%m%d')}.zip",
                mime="application/zip"
            )

def metrics_tab(project_id):
    st.subheader("Project Metrics")
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

def ai_audit_tab(project_id):
    st.subheader("AI Audit")
    st.button("AI Audit", disabled=True)
    st.info("AI Audit functionality coming soon.")


def get_reviews_for_user(user_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('''
        SELECT d.id, d.doc_name, p.name, r.status
        FROM documents d
        JOIN reviews r ON d.id = r.document_id
        JOIN projects p ON d.project_id = p.id
        WHERE r.reviewer_id = ?
    ''', (user_id,))
    data = c.fetchall()
    conn.close()
    return data

def get_review_history(user_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('''
        SELECT d.doc_name, d.version, r.timestamp, r.status, r.comments
        FROM documents d
        JOIN revision_history r ON d.id = r.doc_id
        WHERE r.author_id = ?
    ''', (user_id,))
    data = c.fetchall()
    conn.close()
    return data



def get_all_reviews_for_project(project_id):
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute('''
        SELECT d.doc_name, d.version, r.timestamp, r.status, u.username, r.comments
        FROM documents d
        JOIN revision_history r ON d.id = r.doc_id
        JOIN users u ON r.author_id = u.id
        WHERE d.project_id = ?
    ''', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def reviews_tab(project_id):
    st.subheader("Reviews")
    review_choice = st.radio("", ["My Reviews", "All Reviews"])

    if review_choice == "My Reviews":
        reviews = get_reviews_for_user(st.session_state['user_info'][0])
        if not reviews:
            st.info("You have no documents to review.")
        else:
            with st.expander("Review Items", expanded=True):
                review_options = {f"{r[1]} (v{get_document_details(r[0])[5]}) - Status: {r[3]}" : r[0] for r in reviews}
                selected_review_label = st.selectbox("Select a review to comment on:", list(review_options.keys()))

                if selected_review_label:
                    doc_id = review_options[selected_review_label]
                    doc_details = get_document_details(doc_id)
                    if doc_details:
                        if doc_details[3] == "Code Review": # Check if it's a code review
                            content_data = json.loads(doc_details[4])
                            payload = content_data.get("content", {})
                            if isinstance(payload, str):
                                try: payload = json.loads(payload)
                                except: payload = {}
                            
                            raw_diff = payload.get("raw_diff")
                            if raw_diff:
                                diff_html = colorize_diff_to_html(raw_diff)
                                st.components.v1.html(diff_html, height=600, scrolling=True)
                            else: # Fallback for old format
                                code1 = payload.get("code1", "")
                                code2 = payload.get("code2", "")
                                d = difflib.HtmlDiff()
                                diff_html = d.make_table(code1.splitlines(), code2.splitlines())
                                st.components.v1.html(diff_html, height=600, scrolling=True)
                        else:
                            content_data = json.loads(doc_details[4])
                            st.markdown(content_data.get("content", ""), unsafe_allow_html=True)

            if selected_review_label:
                st.subheader("Add Your Review")
                with st.form(key="review_form", clear_on_submit=True):
                    comment = st.text_area("Comments")
                    new_status = st.selectbox("Status", ["Comment", "Approved", "Needs Revision"])
                    submit_review = st.form_submit_button("Submit Review")
                    if submit_review:
                        doc_id = review_options[selected_review_label]
                        add_review(doc_id, st.session_state['user_info'][0], comment, new_status)
                        st.success("Your review has been submitted.")
                        st.rerun()

    elif review_choice == "All Reviews":
        all_reviews = get_all_reviews_for_project(project_id)
        if not all_reviews:
            st.info("No reviews for this project yet.")
        else:
            df = pd.DataFrame(all_reviews, columns=['Review Item', 'Version', 'Date & Timestamp', 'Document Status', 'Reviewer', 'Comments'])
            st.dataframe(df)

def admin_page():
    st.header("Admin Dashboard")
    st.subheader("Manage Users")
    users = get_all_users()
    df = pd.DataFrame(users, columns=['ID', 'Username', 'Is Admin'])
    st.dataframe(df)

def app_logs_page():
    st.header("Application Logs")
    if 'app_logs' in st.session_state and st.session_state.app_logs:
        for log in reversed(st.session_state.app_logs):
            with st.container(border=True):
                st.write(f"**Timestamp:** {log['timestamp']}")
                st.text("Prompt:")
                st.code(log['prompt'], language='text')
                st.text("Response:")
                st.code(log['response'], language='html')
    else:
        st.info("No log entries yet.")

def help_page():
    st.header("Docsmate Help")
    st.markdown("""
    Welcome to Docsmate, your all-in-one solution for document lifecycle management in regulated engineering industries. This guide will walk you through the key features of the application.

    ## Getting Started

    To begin, you need to sign up with a unique username and email. Once logged in, you can create a new project from the sidebar. Once a project is created, you can select it from the dropdown to access its details and start managing your documents and other resources.

    ## Key Features

    ### Projects

    * **Create New Project**: Use the form in the sidebar to create a new project.
    * **Edit Project**: Select a project and use the "Edit Current Project" section to update its name and description.
    * **Team**: Add registered users of the system to your project team.
    * **Documents**: Manage all your project documents.
    * **Code**: A dedicated space for code reviews with a diff viewer.
    * **Reviews**: A dedicated space for document and code reviews.
        * **My Reviews**: View items assigned to you for review. Select an item to see the content and add your comments.
        * **All Reviews**: See a complete history of all reviews for the project.
    * **Hazards**: A combined table for hazard analysis and risk management, tailored to different standards like Medical (IEC 62304), Automotive (ISO 26262), and General (IEC 61508).
    * **Traceability**: Manage traceability between requirements, design, and tests.
    * **Artifacts**: Download all approved documents as a single zip file.
    * **Metrics**: View project metrics and charts.
    * **AI Audit**: (Coming Soon) Audit trail for AI-assisted actions.

    ### Review Items (Documents and Code)

    * **Create New Review Item**: You can create a new document from scratch, from a template, from an existing document, or with the help of AI. You can also create code review items in the "Code" tab.
    * **Editor**: Use the rich-text editor to create and format your documents. While the editor does not have built-in table creation controls, you can create tables using external tools and paste their HTML or Markdown syntax directly into the editor. For example, you can use online Markdown table generators to create the table structure, then copy and paste the raw Markdown into the editor. The editor will render basic HTML tables.
    * **AI Assist**: Get help from the AI to improve your writing, summarize content, or generate new ideas.
    * **Request Review**: When your item is ready, you can request a review from team members.
    * **Revision History**: Track all changes made to an item over time.
    * **Export to PDF**: Download a PDF version of your document for easy sharing and archiving.

    ### Templates

    * **Create and Manage Templates**: Create reusable document templates to standardize your documentation process.
    * **AI Template Generation**: Use the AI to generate new templates based on your requirements.

    ### Configuration

    * **Knowledge Base**: Upload your own documents to create a local knowledge base for the RAG system.

    ### Admin

    * **User Management**: Admins can view and manage all users in the system.
    """
    )


def migrate_code_review_content():
    """Migrate legacy code review rows where content was double-encoded JSON."""
    conn = sqlite3.connect('docsmate.db', timeout=15)
    c = conn.cursor()
    c.execute("SELECT id, content FROM documents WHERE doc_type='Code Review'")
    rows = c.fetchall()
    updated = 0
    for rid, raw in rows:
        try:
            data = json.loads(raw)
            payload = data.get("content")
            if isinstance(payload, str):
                try:
                    payload_dict = json.loads(payload)
                    # update row with normalized dict
                    new_content = json.dumps({"content": payload_dict})
                    c.execute("UPDATE documents SET content=? WHERE id=?", (new_content, rid))
                    updated += 1
                except Exception:
                    pass
        except Exception:
            pass
    conn.commit()
    conn.close()
    return updated


if __name__ == '__main__':
    init_db()
    try:
        migrated = migrate_code_review_content()
        if migrated:
            print(f"Migrated {migrated} legacy code review item(s).")
    except Exception as e:
        print(f"Migration skipped due to error: {e}")
    main()
