import sqlite3
import hashlib
import json
from datetime import datetime
import os

DB_PATH = '/app/data/db/docsmate.db'

def make_hashes(password):
    return hashlib.sha256(str.encode(password)).hexdigest()

def check_hashes(password, hashed_text):
    if make_hashes(password) == hashed_text:
        return True
    return False

def init_db():
    # Ensure the directory exists
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = sqlite3.connect(DB_PATH, timeout=15)
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
    c.execute("PRAGMA table_info(users)")
    columns = [column[1] for column in c.fetchall()]
    if 'email' not in columns:
        c.execute("ALTER TABLE users ADD COLUMN email TEXT UNIQUE")

    c.execute("SELECT * FROM users WHERE email='aiuser@docsmate.com'")
    if not c.fetchone():
        try:
            add_user('aiuser', 'ai_password', 'aiuser@docsmate.com', is_admin=False)
        except sqlite3.IntegrityError:
            # User already exists, ignore
            pass

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
    
    # Templates table
    c.execute('''
        CREATE TABLE IF NOT EXISTS templates (
            id INTEGER PRIMARY KEY,
            name TEXT UNIQUE,
            content TEXT,
            document_type TEXT,
            created_at DATETIME
        )
    ''')
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
    
    # Documents table
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

    # Training table
    c.execute('''
        CREATE TABLE IF NOT EXISTS training (
            id INTEGER PRIMARY KEY,
            project_id INTEGER,
            question TEXT,
            user_answer TEXT,
            actual_answer TEXT,
            user_id INTEGER,
            timestamp DATETIME,
            FOREIGN KEY(project_id) REFERENCES projects(id),
            FOREIGN KEY(user_id) REFERENCES users(id)
        )
    ''')

    # Audit Gaps table
    c.execute('''
        CREATE TABLE IF NOT EXISTS audit_gaps (
            id INTEGER PRIMARY KEY,
            project_id INTEGER,
            audit_reference TEXT,
            document_name TEXT,
            comments TEXT,
            status TEXT,
            audit_timestamp DATETIME,
            user_data TEXT,
            user_query TEXT,
            context TEXT,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        )
    ''')
    c.execute("PRAGMA table_info(audit_gaps)")
    columns = [column[1] for column in c.fetchall()]
    if 'audit_reference' not in columns:
        c.execute("ALTER TABLE audit_gaps ADD COLUMN audit_reference TEXT")
    if 'audit_timestamp' not in columns:
        c.execute("ALTER TABLE audit_gaps ADD COLUMN audit_timestamp DATETIME")
    if 'user_data' not in columns:
        c.execute("ALTER TABLE audit_gaps ADD COLUMN user_data TEXT")
    if 'user_query' not in columns:
        c.execute("ALTER TABLE audit_gaps ADD COLUMN user_query TEXT")
    if 'context' not in columns:
        c.execute("ALTER TABLE audit_gaps ADD COLUMN context TEXT")

    conn.commit()
    conn.close()

def add_user(username, password, email, is_admin=False):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO users (username, password_hash, email, is_admin) VALUES (?,?,?,?)', (username, make_hashes(password), email, is_admin))
    conn.commit()
    conn.close()

def login_user(email, password):
    conn = sqlite3.connect(DB_PATH, timeout=15)
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
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT username FROM users WHERE id =?', (user_id,))
    data = c.fetchone()
    conn.close()
    return data[0] if data else "Unknown"

def get_all_users():
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT id, username, is_admin FROM users')
    data = c.fetchall()
    conn.close()
    return data

def create_project(name, description, user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO projects (name, description, created_by_user_id) VALUES (?,?,?)', (name, description, user_id))
    conn.commit()
    conn.close()

def get_projects():
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM projects')
    data = c.fetchall()
    conn.close()
    return data

def get_project_details(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM projects WHERE id =?', (project_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_project(project_id, name, description):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('UPDATE projects SET name = ?, description = ? WHERE id = ?', (name, description, project_id))
    conn.commit()
    conn.close()

def add_team_member(user_id, project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO team_members (user_id, project_id) VALUES (?,?)', (user_id, project_id))
    conn.commit()
    conn.close()

def get_team_members(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('''SELECT u.id, u.username, u.email FROM users u JOIN team_members tm ON u.id = tm.user_id WHERE tm.project_id = ?''', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def delete_team_member(user_id, project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('DELETE FROM team_members WHERE user_id = ? AND project_id = ?', (user_id, project_id))
    conn.commit()
    conn.close()

def create_document(project_id, item_name, doc_type, content, version=1, status='Draft'):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO documents (project_id, doc_name, doc_type, content, version, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)',
              (project_id, item_name, doc_type, json.dumps({"content": content}), version, status, now, now))
    conn.commit()
    doc_id = c.lastrowid
    conn.close()
    return doc_id

def get_documents(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM documents WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data
    
def get_document_details(doc_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM documents WHERE id =?', (doc_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_document(doc_id, content, status, comments, author_id):
    print(f"[DEBUG] update_document called: doc_id={doc_id}, status={status}, comments={comments}, author_id={author_id}")
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('UPDATE documents SET content =?, status = ?, updated_at =? WHERE id =?', (json.dumps({"content": content}), status, now, doc_id))
    if status != "Draft":
        add_revision_history(doc_id, status, author_id, comments, c)
    conn.commit()
    conn.close()

def add_risk(project_id, failure_mode, severity, occurrence, detection, rpn, comments, status='New'):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO risks (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status) VALUES (?,?,?,?,?,?,?,?)',
              (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status))
    conn.commit()
    conn.close()

def get_risks(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM risks WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_traceability(project_id, requirement_ref, design_ref, test_ref):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO traceability (project_id, requirement_ref, design_ref, test_ref) VALUES (?,?,?,?)',
              (project_id, requirement_ref, design_ref, test_ref))
    conn.commit()
    conn.close()

def get_traceability(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM traceability WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_review(document_id, reviewer_id, comments, status):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO reviews (document_id, reviewer_id, comments, status) VALUES (?,?,?,?)',
              (document_id, reviewer_id, comments, status))
    if status in ["Approved", "Needs Revision"]:
        c.execute('UPDATE documents SET status = ? WHERE id = ?', (status, document_id))
        add_revision_history(document_id, status, reviewer_id, comments, c)
    conn.commit()
    conn.close()

def get_reviews_for_document(document_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
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

def create_template(name, content, doc_type):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO templates (name, content, document_type, created_at) VALUES (?,?,?,?)', (name, content, doc_type, now))
    conn.commit()
    conn.close()

def get_templates():
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM templates')
    data = c.fetchall()
    conn.close()
    return data

def get_template_details(template_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM templates WHERE id = ?', (template_id,))
    data = c.fetchone()
    conn.close()
    return data

def update_template(template_id, name, content):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('UPDATE templates SET name = ?, content = ? WHERE id = ?', (name, content, template_id))
    conn.commit()
    conn.close()

def add_hazard_traceability(project_id, hazard, cause, effect, risk_control_measure, verification, severity, occurrence, detection, mitigation_notes):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO hazard_traceability (project_id, hazard, cause, effect, risk_control_measure, verification, severity, occurrence, detection, mitigation_notes) VALUES (?,?,?,?,?,?,?,?,?,?)',
              (project_id, hazard, cause, effect, risk_control_measure, verification, severity, occurrence, detection, mitigation_notes))
    conn.commit()
    conn.close()

def get_hazard_traceability(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM hazard_traceability WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_revision_history(doc_id, status, author_id, comments, db_cursor=None):
    print(f"[DEBUG] add_revision_history called: doc_id={doc_id}, status={status}, author_id={author_id}, comments={comments}")
    conn = None
    if not db_cursor:
        conn = sqlite3.connect(DB_PATH, timeout=15)
        db_cursor = conn.cursor()
    
    now = datetime.now()
    db_cursor.execute('INSERT INTO revision_history (doc_id, status, author_id, timestamp, comments) VALUES (?,?,?,?,?)',
              (doc_id, status, author_id, now, comments))
    
    if conn:
        conn.commit()
        conn.close()

def get_revision_history(doc_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM revision_history WHERE doc_id =?', (doc_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_asil_entry(project_id, hazard_desc, severity, exposure, controllability, asil_rating):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO asil_master (project_id, hazard_description, severity, exposure, controllability, asil_rating) VALUES (?,?,?,?,?,?)',
              (project_id, hazard_desc, severity, exposure, controllability, asil_rating))
    conn.commit()
    conn.close()

def get_asil_entries(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM asil_master WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_sil_entry(project_id, hazard_desc, consequence, exposure, avoidance, probability, sil_rating):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('INSERT INTO sil_master (project_id, hazard_description, consequence, exposure, avoidance, probability, sil_rating) VALUES (?,?,?,?,?,?,?)',
              (project_id, hazard_desc, consequence, exposure, avoidance, probability, sil_rating))
    conn.commit()
    conn.close()

def get_sil_entries(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM sil_master WHERE project_id =?', (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def add_audit_gap(project_id, audit_reference, document_name, comments, status, timestamp, user_data, user_query, context):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute("INSERT INTO audit_gaps (project_id, audit_reference, document_name, comments, status, audit_timestamp, user_data, user_query, context) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
              (project_id, audit_reference, document_name, comments, status, timestamp, user_data, user_query, context))
    conn.commit()
    conn.close()

def get_audit_gaps(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute("SELECT id, project_id, audit_reference, document_name, comments, status, audit_timestamp, user_data, user_query, context FROM audit_gaps WHERE project_id = ?", (project_id,))
    data = c.fetchall()
    conn.close()
    return data

def get_audit_references(project_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute("SELECT DISTINCT audit_reference FROM audit_gaps WHERE project_id = ?", (project_id,))
    data = [row[0] for row in c.fetchall()]
    conn.close()
    return data

def get_audit_gaps_by_reference(project_id, audit_reference):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute("SELECT id, project_id, audit_reference, document_name, comments, status, audit_timestamp, user_data, user_query, context FROM audit_gaps WHERE project_id = ? AND audit_reference = ?", (project_id, audit_reference))
    data = c.fetchall()
    conn.close()
    return data

def add_training_question(project_id, question, actual_answer, user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    now = datetime.now()
    c.execute('INSERT INTO training (project_id, question, actual_answer, user_id, timestamp) VALUES (?,?,?,?,?)',
              (project_id, question, actual_answer, user_id, now))
    conn.commit()
    conn.close()

def get_training_questions(project_id, user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM training WHERE project_id =? AND user_id =? AND user_answer IS NULL', (project_id, user_id))
    data = c.fetchall()
    conn.close()
    return data

def update_training_answer(question_id, user_answer):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('UPDATE training SET user_answer =? WHERE id =?', (user_answer, question_id))
    conn.commit()
    conn.close()

def get_training_history(project_id, user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('SELECT * FROM training WHERE project_id =? AND user_id =? AND user_answer IS NOT NULL', (project_id, user_id))
    data = c.fetchall()
    conn.close()
    return data

def get_reviews_for_user(user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    c.execute('''
        SELECT d.id, d.doc_name, d.version, p.name, r.status
        FROM documents d
        JOIN reviews r ON d.id = r.document_id
        JOIN projects p ON d.project_id = p.id
        WHERE r.reviewer_id = ?
    ''', (user_id,))
    data = c.fetchall()
    conn.close()
    return data

def get_review_history(user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
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
    conn = sqlite3.connect(DB_PATH, timeout=15)
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

def get_user_accessible_projects(user_id):
    conn = sqlite3.connect(DB_PATH, timeout=15)
    c = conn.cursor()
    
    # Projects created by the user
    c.execute('SELECT * FROM projects WHERE created_by_user_id = ?', (user_id,))
    created_projects = c.fetchall()

    # Projects where the user is a team member
    c.execute('''
        SELECT p.* FROM projects p
        JOIN team_members tm ON p.id = tm.project_id
        WHERE tm.user_id = ?
    ''', (user_id,))
    team_projects = c.fetchall()

    # Projects where the user has a pending review
    c.execute('''
        SELECT DISTINCT p.* FROM projects p
        JOIN documents d ON p.id = d.project_id
        JOIN reviews r ON d.id = r.document_id
        WHERE r.reviewer_id = ? AND r.status = 'Pending'
    ''', (user_id,))
    review_projects = c.fetchall()

    # Combine and deduplicate projects
    all_projects = {}
    for p in created_projects + team_projects + review_projects:
        all_projects[p[0]] = p
    
    conn.close()
    return list(all_projects.values())

def migrate_code_review_content():
    conn = sqlite3.connect(DB_PATH, timeout=15)
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
