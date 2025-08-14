# backend.py
# FastAPI backend for docsmate.

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import sqlite3
import hashlib
import json
from datetime import datetime

app = FastAPI()

# --- Database Connection ---
def get_db_connection():
    conn = sqlite3.connect('docsmate.db')
    conn.row_factory = sqlite3.Row
    return conn

# --- Pydantic Models ---
class User(BaseModel):
    username: str
    password: str
    is_admin: bool = False

class Project(BaseModel):
    name: str
    description: str
    user_id: int

class Document(BaseModel):
    project_id: int
    doc_name: str
    doc_type: str
    content: str
    version: int = 1
    status: str = 'Draft'

class Risk(BaseModel):
    project_id: int
    failure_mode: str
    severity: int
    occurrence: int
    detection: int
    comments: str

# --- Helper Functions ---
def make_hashes(password):
    return hashlib.sha256(str.encode(password)).hexdigest()

def check_hashes(password, hashed_text):
    return make_hashes(password) == hashed_text

# --- API Endpoints ---

@app.post("/users/")
def add_user_api(user: User):
    conn = get_db_connection()
    try:
        conn.execute('INSERT INTO users (username, password_hash, is_admin) VALUES (?,?,?)',
                     (user.username, make_hashes(user.password), user.is_admin))
        conn.commit()
    except sqlite3.IntegrityError:
        raise HTTPException(status_code=400, detail="Username already exists")
    finally:
        conn.close()
    return {"message": "User created successfully"}

@app.post("/login/")
def login_user_api(user: User):
    conn = get_db_connection()
    db_user = conn.execute('SELECT * FROM users WHERE username =?', (user.username,)).fetchone()
    conn.close()
    if db_user and check_hashes(user.password, db_user['password_hash']):
        return dict(db_user)
    raise HTTPException(status_code=400, detail="Incorrect username or password")

@app.get("/projects/")
def get_projects_api():
    conn = get_db_connection()
    projects = conn.execute('SELECT * FROM projects').fetchall()
    conn.close()
    return [dict(row) for row in projects]

@app.post("/projects/")
def create_project_api(project: Project):
    conn = get_db_connection()
    conn.execute('INSERT INTO projects (name, description, created_by_user_id) VALUES (?,?,?)',
                 (project.name, project.description, project.user_id))
    conn.commit()
    conn.close()
    return {"message": "Project created successfully"}

@app.get("/documents/{project_id}")
def get_documents_api(project_id: int):
    conn = get_db_connection()
    docs = conn.execute('SELECT * FROM documents WHERE project_id =?', (project_id,)).fetchall()
    conn.close()
    return [dict(row) for row in docs]

@app.post("/documents/")
def create_document_api(doc: Document):
    conn = get_db_connection()
    now = datetime.now()
    conn.execute('INSERT INTO documents (project_id, doc_name, doc_type, content, version, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)',
                 (doc.project_id, doc.doc_name, doc.doc_type, json.dumps({"content": doc.content}), doc.version, doc.status, now, now))
    conn.commit()
    conn.close()
    return {"message": "Document created successfully"}

@app.post("/risks/")
def add_risk_api(risk: Risk):
    conn = get_db_connection()
    rpn = risk.severity * risk.occurrence * risk.detection
    conn.execute('INSERT INTO risks (project_id, failure_mode, severity, occurrence, detection, rpn, comments, status) VALUES (?,?,?,?,?,?,?,?)',
                 (risk.project_id, risk.failure_mode, risk.severity, risk.occurrence, risk.detection, rpn, risk.comments, 'New'))
    conn.commit()
    conn.close()
    return {"message": "Risk added successfully"}

