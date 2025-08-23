from fastapi import FastAPI, Depends, HTTPException, UploadFile, File, Form
from pydantic import BaseModel
import database
import os
import config
from typing import List, Optional
import json
from datetime import datetime
import knowledge_base as kb
from langchain_community.vectorstores import FAISS
import ai_integration

app = FastAPI()

@app.get("/health")
def health_check():
    return {"status": "ok"}

# Pydantic Models
class User(BaseModel):
    username: str
    email: str
    password: str

class Login(BaseModel):
    email: str
    password: str

class Project(BaseModel):
    name: str
    description: str
    user_id: int

class Document(BaseModel):
    project_id: int
    doc_name: str
    doc_type: str
    content: str
    version: Optional[int] = 1
    status: Optional[str] = 'Draft'

class DocumentUpdate(BaseModel):
    content: str
    status: str
    comments: str
    author_id: int

class Review(BaseModel):
    document_id: int
    reviewer_id: int
    comments: str
    status: str

class Template(BaseModel):
    name: str
    content: str
    document_type: str

class TeamMember(BaseModel):
    user_id: int
    project_id: int

class Traceability(BaseModel):
    project_id: int
    requirement_ref: str
    design_ref: str
    test_ref: str

class HazardMedical(BaseModel):
    project_id: int
    hazard: str
    cause: str
    effect: str
    risk_control_measure: str
    verification: str
    severity: int
    occurrence: int
    detection: int
    mitigation_notes: str

class HazardAutomotive(BaseModel):
    project_id: int
    hazard_description: str
    severity: str
    exposure: str
    controllability: str

class HazardGeneral(BaseModel):
    project_id: int
    hazard_description: str
    consequence: str
    exposure: str
    avoidance: str
    probability: str

class TrainingQuestion(BaseModel):
    project_id: int
    question: str
    actual_answer: str
    user_id: int

class TrainingAnswer(BaseModel):
    user_answer: str

class KnowledgeBaseUpdate(BaseModel):
    urls: Optional[List[str]] = None

class KnowledgeBaseQuery(BaseModel):
    query: str


@app.on_event("startup")
async def startup_event():
    database.init_db()
    if not os.path.exists(config.UPLOAD_DIRECTORY):
        os.makedirs(config.UPLOAD_DIRECTORY)
    try:
        migrated = database.migrate_code_review_content()
        if migrated:
            print(f"Migrated {migrated} legacy code review item(s).")
    except Exception as e:
        print(f"Migration skipped due to error: {e}")

@app.get("/")
def read_root():
    return {"Hello": "World"}

# User Management
@app.post("/signup")
def signup(user: User):
    try:
        database.add_user(user.username, user.password, user.email)
        return {"message": "User created successfully"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))

@app.post("/login")
def login(login: Login):
    user = database.login_user(login.email, login.password)
    if user:
        return {"user": user}
    else:
        raise HTTPException(status_code=401, detail="Incorrect email or password")

@app.get("/users")
def get_users():
    users_data = database.get_all_users()
    users_list = []
    for u in users_data:
        users_list.append({
            "id": u[0],
            "username": u[1],
            "is_admin": u[2]
        })
    return users_list

# Project Management
@app.post("/projects")
def create_project(project: Project):
    database.create_project(project.name, project.description, project.user_id)
    return {"message": "Project created successfully"}

@app.get("/projects")
def get_projects():
    projects_data = database.get_projects()
    projects_list = []
    for p in projects_data:
        projects_list.append({
            "id": p[0],
            "name": p[1],
            "description": p[2],
            "created_by_user_id": p[3]
        })
    return projects_list

@app.get("/projects/{project_id}")
def get_project_details(project_id: int):
    project_data = database.get_project_details(project_id)
    if project_data:
        return {
            "id": project_data[0],
            "name": project_data[1],
            "description": project_data[2],
            "created_by_user_id": project_data[3]
        }
    return None

@app.put("/projects/{project_id}")
def update_project(project_id: int, project: Project):
    database.update_project(project_id, project.name, project.description)
    return {"message": "Project updated successfully"}

@app.get("/projects/user/{user_id}")
def get_user_accessible_projects(user_id: int):
    projects_data = database.get_user_accessible_projects(user_id)
    # Convert list of tuples to list of dictionaries
    projects_list = []
    for p in projects_data:
        projects_list.append({
            "id": p[0],
            "name": p[1],
            "description": p[2],
            "created_by_user_id": p[3]
        })
    return projects_list

# Document Management
@app.get("/documents/{doc_id}/history")
def get_document_history(doc_id: int):
    history_data = database.get_revision_history(doc_id)
    history_list = []
    for h in history_data:
        history_list.append({
            "id": h[0],
            "doc_id": h[1],
            "status": h[2],
            "author_id": h[3],
            "timestamp": h[4],
            "comments": h[5]
        })
    return history_list

@app.post("/documents")
def create_document(doc: Document):
    doc_id = database.create_document(doc.project_id, doc.doc_name, doc.doc_type, doc.content, doc.version, doc.status)
    return {"id": doc_id}

@app.get("/documents/project/{project_id}")
def get_documents(project_id: int, status: Optional[str] = None):
    docs_data = database.get_documents(project_id)
    docs_list = []
    for d in docs_data:
        doc_dict = {
            "id": d[0],
            "project_id": d[1],
            "doc_name": d[2],
            "doc_type": d[3],
            "content": d[4],
            "version": d[5],
            "status": d[6],
            "created_at": d[7],
            "updated_at": d[8]
        }
        if status and doc_dict["status"] != status:
            continue
        docs_list.append(doc_dict)
    return docs_list

@app.get("/documents/{doc_id}")
def get_document_details(doc_id: int):
    doc_data = database.get_document_details(doc_id)
    if doc_data:
        return {
            "id": doc_data[0],
            "project_id": doc_data[1],
            "doc_name": doc_data[2],
            "doc_type": doc_data[3],
            "content": doc_data[4],
            "version": doc_data[5],
            "status": doc_data[6],
            "created_at": doc_data[7],
            "updated_at": doc_data[8]
        }
    return None

@app.put("/documents/{doc_id}")
def update_document(doc_id: int, doc: DocumentUpdate):
    database.update_document(doc_id, doc.content, doc.status, doc.comments, doc.author_id)
    return {"message": "Document updated successfully"}

@app.get("/document_types")
def get_document_types():
    return config.DOCUMENT_TYPES

# Template Management
@app.post("/templates")
def create_template(template: Template):
    database.create_template(template.name, template.content, template.document_type)
    return {"message": "Template created successfully"}

@app.get("/templates")
def get_templates():
    templates_data = database.get_templates()
    templates_list = []
    for t in templates_data:
        templates_list.append({
            "id": t[0],
            "name": t[1],
            "content": t[2],
            "document_type": t[3],
            "created_at": t[4]
        })
    return templates_list

@app.get("/templates/{template_id}")
def get_template_details(template_id: int):
    template_data = database.get_template_details(template_id)
    if template_data:
        return {
            "id": template_data[0],
            "name": template_data[1],
            "content": template_data[2],
            "document_type": template_data[3],
            "created_at": template_data[4]
        }
    return None

@app.put("/templates/{template_id}")
def update_template(template_id: int, template: Template):
    database.update_template(template_id, template.name, template.content)
    return {"message": "Template updated successfully"}

# Review Management
@app.post("/reviews")
def add_review(review: Review):
    database.add_review(review.document_id, review.reviewer_id, review.comments, review.status)
    return {"message": "Review added successfully"}

@app.get("/reviews/user/{user_id}")
def get_reviews_for_user(user_id: int):
    reviews_data = database.get_reviews_for_user(user_id)
    reviews_list = []
    for r in reviews_data:
        reviews_list.append({
            "document_id": r[0],
            "doc_name": r[1],
            "version": r[2],
            "project_name": r[3],
            "status": r[4]
        })
    return reviews_list

@app.get("/reviews/project/{project_id}")
def get_all_reviews_for_project(project_id: int):
    reviews_data = database.get_all_reviews_for_project(project_id)
    reviews_list = []
    for r in reviews_data:
        reviews_list.append({
            "doc_name": r[0],
            "version": r[1],
            "timestamp": r[2],
            "status": r[3],
            "reviewer": r[4],
            "comments": r[5]
        })
    return reviews_list

# Team Management
@app.post("/team_members")
def add_team_member(team_member: TeamMember):
    database.add_team_member(team_member.user_id, team_member.project_id)
    return {"message": "Team member added successfully"}

@app.get("/team_members/{project_id}")
def get_team_members(project_id: int):
    team_members_data = database.get_team_members(project_id)
    team_members_list = []
    for tm in team_members_data:
        team_members_list.append({
            "id": tm[0],
            "username": tm[1],
            "email": tm[2]
        })
    return team_members_list

@app.delete("/team_members/{project_id}/{user_id}")
def delete_team_member(project_id: int, user_id: int):
    database.delete_team_member(user_id, project_id)
    return {"message": "Team member deleted successfully"}

# Traceability Management
@app.post("/traceability")
def add_traceability(trace: Traceability):
    database.add_traceability(trace.project_id, trace.requirement_ref, trace.design_ref, trace.test_ref)
    return {"message": "Traceability link added successfully"}

@app.get("/traceability/{project_id}")
def get_traceability(project_id: int):
    traceability_data = database.get_traceability(project_id)
    traceability_list = []
    for t in traceability_data:
        traceability_list.append({
            "id": t[0],
            "project_id": t[1],
            "requirement_ref": t[2],
            "design_ref": t[3],
            "test_ref": t[4]
        })
    return traceability_list


@app.get("/options/hazards/automotive/severity")
def get_automotive_severity_options():
    options = ["S0: No injuries", "S1: Light to moderate injuries", "S2: Severe injuries", "S3: Life-threatening injuries (survival uncertain), fatal injuries"]
    print(f"[DEBUG] ASIL Severity Options (Hardcoded): {options}")
    return options

@app.get("/options/hazards/automotive/exposure")
def get_automotive_exposure_options():
    return list(config.ASIL_EXPOSURE.keys())

@app.get("/options/hazards/automotive/controllability")
def get_automotive_controllability_options():
    return list(config.ASIL_CONTROLLABILITY.keys())

@app.get("/options/hazards/general/consequence")
def get_general_consequence_options():
    return list(config.SIL_CONSEQUENCE.keys())

@app.get("/options/hazards/general/exposure")
def get_general_exposure_options():
    return list(config.SIL_EXPOSURE.keys())

@app.get("/options/hazards/general/avoidance")
def get_general_avoidance_options():
    return list(config.SIL_AVOIDANCE.keys())

@app.get("/options/hazards/general/probability")
def get_general_probability_options():
    return list(config.SIL_PROBABILITY.keys())

# Hazard Management
@app.post("/hazards/medical")
def add_hazard_medical(hazard: HazardMedical):
    database.add_hazard_traceability(hazard.project_id, hazard.hazard, hazard.cause, hazard.effect, hazard.risk_control_measure, hazard.verification, hazard.severity, hazard.occurrence, hazard.detection, hazard.mitigation_notes)
    return {"message": "Medical hazard added successfully"}

@app.get("/hazards/medical/{project_id}")
def get_hazard_medical(project_id: int):
    hazards_data = database.get_hazard_traceability(project_id)
    hazards_list = []
    for h in hazards_data:
        hazards_list.append({
            "id": h[0],
            "project_id": h[1],
            "hazard": h[2],
            "cause": h[3],
            "effect": h[4],
            "risk_control_measure": h[5],
            "verification": h[6],
            "severity": h[7],
            "occurrence": h[8],
            "detection": h[9],
            "mitigation_notes": h[10]
        })
    return hazards_list

@app.post("/hazards/automotive")
def add_hazard_automotive(hazard: HazardAutomotive):
    asil_rating = config.ASIL_RATING_TABLE.get(config.ASIL_SEVERITY[hazard.severity] + config.ASIL_EXPOSURE[hazard.exposure] + config.ASIL_CONTROLLABILITY[hazard.controllability], "QM")
    database.add_asil_entry(hazard.project_id, hazard.hazard_description, hazard.severity, hazard.exposure, hazard.controllability, asil_rating)
    return {"asil_rating": asil_rating}

@app.get("/hazards/automotive/{project_id}")
def get_hazard_automotive(project_id: int):
    asil_data = database.get_asil_entries(project_id)
    asil_list = []
    for a in asil_data:
        asil_list.append({
            "id": a[0],
            "project_id": a[1],
            "hazard_description": a[2],
            "severity": a[3],
            "exposure": a[4],
            "controllability": a[5],
            "asil_rating": a[6]
        })
    return asil_list

@app.post("/hazards/general")
def add_hazard_general(hazard: HazardGeneral):
    sil_rating = config.SIL_RATING_TABLE.get(
        (config.SIL_PROBABILITY[hazard.probability], config.SIL_CONSEQUENCE[hazard.consequence], config.SIL_EXPOSURE[hazard.exposure], config.SIL_AVOIDANCE[hazard.avoidance]),
        "Invalid"
    )
    database.add_sil_entry(hazard.project_id, hazard.hazard_description, hazard.consequence, hazard.exposure, hazard.avoidance, hazard.probability, sil_rating)
    return {"sil_rating": sil_rating}

@app.get("/hazards/general/{project_id}")
def get_hazard_general(project_id: int):
    sil_data = database.get_sil_entries(project_id)
    sil_list = []
    for s in sil_data:
        sil_list.append({
            "id": s[0],
            "project_id": s[1],
            "hazard_description": s[2],
            "consequence": s[3],
            "exposure": s[4],
            "avoidance": s[5],
            "probability": s[6],
            "sil_rating": s[7]
        })
    return sil_list

# Audit Management
@app.post("/audit/{project_id}")
def run_audit(project_id: int):
    try:
        # Fetch all documents from the project to use as context for the audit
        docs = database.get_documents(project_id)
        context = "\n".join([json.loads(d[4]).get("content", "") for d in docs]) # d[4] is the content

        # Get the audit prompt from config
        audit_prompt_template = config.GENERAL_AI_PROMPTS["Audit Query"]
        full_prompt = f"Context:\n{context}\n\n{audit_prompt_template}"

        # Call the LLM
        ai_response = ai_integration.llm_client_instance.generate_text(full_prompt)

        # For now, just store a simplified audit gap based on the AI response
        # A more sophisticated implementation would parse the AI response and create multiple audit gaps
        database.add_audit_gap(
            project_id=project_id,
            audit_reference=f"Audit-{datetime.now().strftime('%Y%m%d-%H%M%S')}",
            document_name="Overall Project Audit",
            comments=ai_response, # Store AI response as comments for now
            status="New",
            timestamp=datetime.now(),
            user_data="N/A", # Frontend doesn't provide this yet
            user_query=audit_prompt_template,
            context=context
        )

        return {"message": "AI Audit initiated and gaps recorded."}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/audit/report/{project_id}/{audit_reference}")
def get_audit_report(project_id: int, audit_reference: str):
    gaps_data = database.get_audit_gaps_by_reference(project_id, audit_reference)
    if gaps_data:
        # Assuming the audit report is in the comments of the first gap for simplicity
        # A more robust solution would aggregate all comments or have a dedicated report field
        report_content = gaps_data[0][4]
        return report_content
    raise HTTPException(status_code=404, detail="Audit report not found.")

@app.get("/audit/references/{project_id}")
def get_audit_references(project_id: int):
    return database.get_audit_references(project_id)

@app.get("/audit/gaps/{project_id}/{audit_reference}")
def get_audit_gaps_by_reference(project_id: int, audit_reference: str):
    gaps_data = database.get_audit_gaps_by_reference(project_id, audit_reference)
    gaps_list = []
    for g in gaps_data:
        gaps_list.append({
            "id": g[0],
            "project_id": g[1],
            "audit_reference": g[2],
            "document_name": g[3],
            "comments": g[4],
            "status": g[5],
            "audit_timestamp": g[6],
            "user_data": g[7],
            "user_query": g[8],
            "context": g[9]
        })
    return gaps_list

@app.get("/prompts/new_document/{doc_type}")
def get_new_document_prompt(doc_type: str):
    prompt = config.NEW_DOCUMENT_PROMPTS.get(doc_type)
    if prompt:
        return {"prompt": prompt}
    raise HTTPException(status_code=404, detail="Prompt not found for this document type.")

@app.get("/prompts/training")
def get_training_prompt():
    return {"prompt": "Prepare a set of at most 5 True/False type questions, related to SOPs from the knowledge base. Each question must end with either '(True)' or '(False)'."}

# Training Management
@app.post("/training/generate")
def generate_training_questions(req: dict):
    project_id = req.get("project_id")
    user_id = req.get("user_id")
    prompt = req.get("prompt")
    
    # Simplified: Get all documents from the project as context
    docs = database.get_documents(project_id)
    context = "\n".join([d[4] for d in docs]) # d[4] is the content
    
    full_prompt = f"{prompt}\n\nContext:\n{context}"
    
    ai_response = ai_integration.llm_client_instance.generate_text(full_prompt)
    
    # Naive parsing of the response. Assumes "Question: ... Answer: ..." format
    questions = []
    for block in ai_response.split("Question:"):
        if "Answer:" in block:
            question_text, answer_text = block.split("Answer:")
            question_text = question_text.strip()
            answer_text = answer_text.strip()
            if question_text and answer_text in ["True", "False"]:
                database.add_training_question(project_id, question_text, answer_text, user_id)
                questions.append({"question": question_text, "answer": answer_text})

    return {"message": f"{len(questions)} training questions generated."}


@app.get("/training/questions/{project_id}/{user_id}")
def get_training_questions(project_id: int, user_id: int):
    questions_data = database.get_training_questions(project_id, user_id)
    questions_list = []
    for q in questions_data:
        questions_list.append({
            "id": q[0],
            "project_id": q[1],
            "question": q[2],
            "user_answer": q[3],
            "actual_answer": q[4],
            "user_id": q[5],
            "timestamp": q[6]
        })
    return questions_list

@app.put("/training/answer/{question_id}")
def update_training_answer(question_id: int, answer: TrainingAnswer):
    database.update_training_answer(question_id, answer.user_answer)
    return {"message": "Answer updated"}

@app.get("/training/history/{project_id}/{user_id}")
def get_training_history(project_id: int, user_id: int):
    history_data = database.get_training_history(project_id, user_id)
    history_list = []
    for h in history_data:
        history_list.append({
            "id": h[0],
            "project_id": h[1],
            "question": h[2],
            "user_answer": h[3],
            "actual_answer": h[4],
            "user_id": h[5],
            "timestamp": h[6]
        })
    return history_list

# AI Integration
@app.post("/generate_text")
def generate_text(req: dict):
    prompt = req.get("prompt")
    if not prompt:
        raise HTTPException(status_code=400, detail="Prompt is required.")
    try:
        response = ai_integration.llm_client_instance.generate_text(prompt)
        return {"text": response}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

import difflib

@app.post("/colorize_diff")
def colorize_diff(req: dict):
    diff_text = req.get("diff_text", "")
    if not diff_text:
        return {"html": ""}

    html_lines = []
    for line in diff_text.splitlines():
        if line.startswith('+'):
            html_lines.append(f'<span style="color: green;">{line}</span>')
        elif line.startswith('-'):
            html_lines.append(f'<span style="color: red;">{line}</span>')
        else:
            html_lines.append(line)
    
    return {"html": "<pre>" + "<br>".join(html_lines) + "</pre>"}

# Knowledge Base
@app.post("/knowledge_base/upload")
async def upload_knowledge_base_files(files: List[UploadFile] = File(...)):
    saved_files = []
    for file in files:
        file_path = os.path.join(config.UPLOAD_DIRECTORY, file.filename)
        with open(file_path, "wb") as buffer:
            buffer.write(await file.read())
        saved_files.append(file.filename)
    return {"message": f"Files uploaded successfully: {', '.join(saved_files)}"}

@app.post("/knowledge_base/update")
def update_knowledge_base(update_data: KnowledgeBaseUpdate):
    try:
        all_text = ""
        if update_data.urls:
            all_text += kb.scrape_websites(update_data.urls)
        
        all_text += kb.process_uploaded_files(config.UPLOAD_DIRECTORY)

        if not all_text:
            return {"message": "No new content to add."}

        chunks = kb.chunk_text(all_text)
        kb.create_and_store_embeddings(chunks)
        
        # Clean up uploaded files after processing
        for filename in os.listdir(config.UPLOAD_DIRECTORY):
            os.remove(os.path.join(config.UPLOAD_DIRECTORY, filename))
            
        return {"message": "Knowledge base updated successfully!"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/knowledge_base/reset")
def reset_knowledge_base():
    try:
        kb.reset_knowledge_base()
        return {"message": "Knowledge base reset successfully."}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/knowledge_base/query")
def query_knowledge_base(query: KnowledgeBaseQuery):
    try:
        embeddings = kb.get_embeddings_model()
        faiss_index_path = config.FAISS_INDEX_PATH
        
        if not os.path.exists(faiss_index_path):
            raise HTTPException(status_code=404, detail="Knowledge base not found. Please update it first.")

        vector_store = FAISS.load_local(faiss_index_path, embeddings, allow_dangerous_deserialization=True)
        
        context_docs = kb.query_knowledge_base(query.query, vector_store)
        answer = kb.get_answer_from_llm(query.query, context_docs)
        return {"answer": answer}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/knowledge_base/status")
def get_knowledge_base_status():
    faiss_index_path = config.FAISS_INDEX_PATH
    if os.path.exists(os.path.join(faiss_index_path, "index.faiss")) and os.path.exists(os.path.join(faiss_index_path, "index.pkl")):
        return {"status": "ready"}
    else:
        return {"status": "not_ready"}



# PDF Generation
@app.post("/generate_pdf")
def generate_pdf(req: dict):
    # This is a complex function, for now we just return a message
    return {"pdf": ""}