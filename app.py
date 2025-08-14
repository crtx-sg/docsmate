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
                response = requests.post(f"http://localhost:8000/login/", json={"username": username, "password": password})
                if response.status_code == 200:
                    st.session_state['logged_in'] = True
                    st.session_state['user_info'] = response.json()
                    st.rerun()
                else:
                    st.warning("Incorrect Username/Password")
        elif choice == "SignUp":
            st.subheader("Create New Account")
            new_user = st.text_input("Username")
            new_password = st.text_input("Password", type='password')
            if st.button("Signup"):
                response = requests.post(f"http://localhost:8000/users/", json={"username": new_user, "password": new_password})
                if response.status_code == 200:
                    st.success("You have successfully created an account")
                    st.info("Go to Login Menu to login")
                else:
                    st.error("Username already exists.")
    else:
        st.sidebar.subheader(f"Welcome {st.session_state['user_info']['username']}")
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
        elif page == "Admin" and st.session_state['user_info']['is_admin']:
            admin_page()
        elif page == "Admin" and not st.session_state['user_info']['is_admin']:
            st.warning("You do not have admin privileges.")

def projects_page():
    st.sidebar.header("Project Management")
    
    with st.sidebar.expander("Create New Project"):
        with st.form("new_project_form_sidebar"):
            project_name = st.text_input("Project Name")
            project_desc = st.text_area("Description")
            submitted = st.form_submit_button("Create Project")
            if submitted:
                requests.post(f"http://localhost:8000/projects/", json={"name": project_name, "description": project_desc, "user_id": st.session_state['user_info']['id']})
                st.success("Project created successfully!")
                st.rerun()

    response = requests.get(f"http://localhost:8000/projects/")
    projects = response.json()
    if not projects:
        st.header("Projects")
        st.info("No projects found. Create one from the sidebar to get started.")
    else:
        project_names = [p['name'] for p in projects]
        selected_project_name = st.sidebar.selectbox("Select a project", project_names, key="project_selector")
        selected_project = next((p for p in projects if p['name'] == selected_project_name), None)
        if selected_project:
            with st.sidebar.expander("Edit Current Project"):
                with st.form("edit_project_form_sidebar"):
                    st.write(f"Editing: **{selected_project['name']}**")
                    new_project_name = st.text_input("New Project Name", value=selected_project['name'])
                    new_project_desc = st.text_area("New Description", value=selected_project['description'])
                    update_submitted = st.form_submit_button("Update Project")
                    if update_submitted:
                        # This needs a backend endpoint
                        st.info("Update functionality coming soon.")
                        # update_project(selected_project['id'], new_project_name, new_project_desc)
                        # st.success("Project updated successfully!")
                        # st.rerun()
            project_detail_page(selected_project['id'])

def project_detail_page(project_id):
    response = requests.get(f"http://localhost:8000/projects/")
    project_details = next((p for p in response.json() if p['id'] == project_id), None)
    
    st.header(f"Project: {project_details['name']}")
    st.write(project_details['description'])
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
    # ... (rest of the document tab logic, adapted for API calls)

def team_tab(project_id):
    st.subheader("Team Members")
    # ... (team tab logic, adapted for API calls)

def risks_tab(project_id):
    st.subheader("Risk Management")
    # ... (risks tab logic, adapted for API calls)

def traceability_tab(project_id):
    st.subheader("Traceability Matrix")
    # ... (traceability tab logic, adapted for API calls)

def templates_page():
    st.header("Document Templates")
    # ... (templates page logic, adapted for API calls)

def config_page():
    st.header("AI Configuration and Knowledge Base")
    # ... (config page logic)

def admin_page():
    st.header("Admin Dashboard")
    # ... (admin page logic, adapted for API calls)

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
    main()

