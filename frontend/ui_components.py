import streamlit as st
import pandas as pd
import json
import os
from datetime import datetime
import zipfile
import re
import requests
from streamlit_quill import st_quill
import difflib

API_URL = "http://backend:8000"

def dashboard_page():
    st.header(f"Welcome, {st.session_state['user_info'][1]}!")

    user_id = st.session_state['user_info'][0]

    # --- Quick Stats ---
    st.subheader("Quick Stats")
    col1, col2, col3, col4 = st.columns(4)

    accessible_projects = requests.get(f"{API_URL}/projects/user/{user_id}").json()
    with col1:
        st.metric("Total Projects Accessible", len(accessible_projects))

    pending_reviews = requests.get(f"{API_URL}/reviews/user/{user_id}").json()
    pending_review_count = sum(1 for r in pending_reviews if r['status'] == 'Pending')
    with col2:
        st.metric("Documents Awaiting My Review", pending_review_count)

    pending_training_questions = requests.get(f"{API_URL}/training/questions/{user_id}/{user_id}").json()
    with col3:
        st.metric("Pending Training Questions", len(pending_training_questions))
    
    training_history = requests.get(f"{API_URL}/training/history/{user_id}/{user_id}").json()
    if training_history:
        df_training = pd.DataFrame(training_history)
        total_correct = df_training['user_answer'].eq(df_training['actual_answer']).sum()
        total_questions = len(df_training)
        overall_score = f"{total_correct}/{total_questions}" if total_questions > 0 else "N/A"
        overall_percentage = f"({total_correct/total_questions:.0%})" if total_questions > 0 else ""
    else:
        overall_score = "N/A"
        overall_percentage = ""

    with col4:
        st.metric("My Overall Training Score", f"{overall_score} {overall_percentage}")

    st.markdown("---")

    # --- My Action Items / To-Do List ---
    st.subheader("My Action Items")
    
    if pending_review_count > 0:
        st.write("#### Documents Awaiting Your Review:")
        for review in pending_reviews:
            if review['status'] == 'Pending':
                doc_details = requests.get(f"{API_URL}/documents/{review['document_id']}").json()
                if doc_details:
                    st.info(f"**Project:** {review['project_name']} - **Document:** {doc_details['doc_name']} (Status: {review['status']})")
    else:
        st.info("No documents currently awaiting your review.")

    if len(pending_training_questions) > 0:
        st.write("#### Pending Training Questions:")
        st.info(f"You have {len(pending_training_questions)} unanswered training questions. Go to the **Training** tab in your project to answer them.")
    else:
        st.info("No pending training questions.")

    st.markdown("---")

    # --- My Projects Overview ---
    st.subheader("My Projects Overview")
    if accessible_projects:
        st.dataframe(accessible_projects, use_container_width=True)
    else:
        st.info("You are not associated with any projects yet. Create a new project from the sidebar.")

    st.markdown("---")

    # --- My Recent Document Activity ---
    st.subheader("My Recent Document Activity")
    st.info("Recent document activity will be displayed here.")

    st.markdown("---")

    # --- My Performance / Metrics (Placeholder) ---
    st.subheader("My Performance")
    st.info("Performance metrics and charts will be displayed here.")

def projects_page():
    st.sidebar.header("Project Management")
    
    with st.sidebar.expander("Create New Project"):
        with st.form("new_project_form_sidebar"):
            project_name = st.text_input("Project Name")
            project_desc = st.text_area("Description")
            submitted = st.form_submit_button("Create Project")
            if submitted:
                requests.post(f"{API_URL}/projects", json={"name": project_name, "description": project_desc, "user_id": st.session_state['user_info'][0]})
                st.success("Project created successfully!")
                st.rerun()

    projects = requests.get(f"{API_URL}/projects/user/{st.session_state['user_info'][0]}").json()
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
                        requests.put(f"{API_URL}/projects/{selected_project['id']}", json={"name": new_project_name, "description": new_project_desc})
                        st.success("Project updated successfully!")
                        st.rerun()
            
            project_detail_page(selected_project['id'])

def project_detail_page(project_id):
    project_details = requests.get(f"{API_URL}/projects/{project_id}").json()
    st.header(f"Project: {project_details['name']}")
    st.write(project_details['description'])
    tabs = st.tabs(["Documents", "Code", "Reviews", "Approved", "Team", "Hazards", "Traceability", "Audit", "Training"])
    with tabs[0]:
        documents_tab(project_id)
    with tabs[1]:
        code_tab(project_id)
    with tabs[2]:
        reviews_tab(project_id)
    with tabs[3]:
        artifacts_tab(project_id) # This is now the 'Approved' tab
    with tabs[4]:
        team_tab(project_id)
    with tabs[5]:
        hazard_traceability_tab(project_id)
    with tabs[6]:
        traceability_tab(project_id)
    with tabs[7]:
        audit_tab(project_id)
    with tabs[8]:
        training_tab(project_id)

def documents_tab(project_id):
    st.subheader("Documents")
    project_details = requests.get(f"{API_URL}/projects/{project_id}").json()
    project_description = project_details['description'] if project_details else ""

    # --- Create New Document Section ---
    with st.expander("Create New Document", expanded=False):
        source_options = ["From Scratch", "From a Template", "From an Existing Document"]
        doc_type = None
        content_source = ""
        version = 1
        doc_name_suggestion = ""

        source_choice = st.radio("Create document from:", source_options, key=f"doc_create_source_{project_id}")
        
        if source_choice == "From Scratch":
            doc_type = st.selectbox("Select Document Type", requests.get(f"{API_URL}/document_types").json(), key=f"scratch_type_{project_id}")
        elif source_choice == "From a Template":
            templates = requests.get(f"{API_URL}/templates").json()
            if templates:
                template_map = {t['name']: (t['content'], t['document_type']) for t in templates}
                selected_template_name = st.selectbox("Select a template", options=list(template_map.keys()), key=f"template_select_{project_id}")
                content_source, doc_type = template_map[selected_template_name]
                doc_name_suggestion = selected_template_name
            else:
                st.warning("No templates found.")
        elif source_choice == "From an Existing Document":
            existing_docs = requests.get(f"{API_URL}/documents/project/{project_id}").json()
            if existing_docs:
                doc_map = {f"{d['doc_name']}(v{d['version']})": (json.loads(d['content']).get("content", ""), d['doc_type'], d['version'], d['doc_name']) for d in existing_docs}
                selected_doc_name_key = st.selectbox("Select a source document", options=list(doc_map.keys()), key=f"existing_doc_select_{project_id}")
                content_source, doc_type, version, doc_name_suggestion = doc_map[selected_doc_name_key]
                version += 1
            else:
                st.warning("No existing documents in this project.")
        
        if 'ai_generated_content' in st.session_state:
            st.success("AI content generated. Review and create the document.")
            content_source = st.session_state.ai_generated_content
        
        item_name = st.text_input("Enter Document Name", value=doc_name_suggestion, key=f"new_doc_name_{project_id}")

        if st.button("Create Document", key=f"create_doc_btn_{project_id}"):
            if item_name and doc_type:
                response = requests.post(f"{API_URL}/documents", json={"project_id": project_id, "doc_name": item_name, "doc_type": doc_type, "content": content_source, "version": version})
                doc_id = response.json()['id']
                st.session_state.last_created_doc_id = doc_id
                if 'ai_generated_content' in st.session_state:
                    del st.session_state.ai_generated_content
                st.success(f"Document '{item_name}' created successfully!")
                st.rerun()
            else:
                st.error("Document Name and Type are required.")

    st.markdown("---")
    
    # --- Existing Documents Selection ---
    docs = requests.get(f"{API_URL}/documents/project/{project_id}").json()
    if not docs:
        st.info("No documents in this project yet. Create one above to get started.")
        return # Exit function if no documents to display/edit

    doc_display_names = [f"{d['doc_name']}(v{d['version']})" for d in docs]
    doc_ids = [d['id'] for d in docs]

    default_index = 0
    if 'last_created_doc_id' in st.session_state and st.session_state.last_created_doc_id in doc_ids:
        default_index = doc_ids.index(st.session_state.last_created_doc_id)
        del st.session_state.last_created_doc_id

    selected_doc_name_display = st.selectbox("Select a document to view or edit", doc_display_names, index=default_index, key=f"doc_select_{project_id}")
    
    if selected_doc_name_display:
        selected_doc_index = doc_display_names.index(selected_doc_name_display)
        selected_doc_info = docs[selected_doc_index]
        doc_details = requests.get(f"{API_URL}/documents/{selected_doc_info['id']}").json()

        if doc_details:
            doc_id, _, doc_name, doc_type, content_json, version, status, _, updated_at = doc_details.values()
            content_data = json.loads(content_json)
            
            st.markdown(f"### {doc_name} (v{version}) - Status: {status}")

            # --- Document Editor Actions ---
            col_ai, col_pdf = st.columns([0.15, 0.15])
            with col_ai:
                if st.button("✨ AI Assist", key=f"ai_assist_{doc_id}"):
                    st.session_state.ai_assist_doc_id = doc_id if st.session_state.get('ai_assist_doc_id') != doc_id else None
            with col_pdf:
                pdf_data = requests.post(f"{API_URL}/generate_pdf", json={"html_content": content_data.get("content", "")}).content
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

            # --- AI Assistant Section (Conditional) ---
            if st.session_state.get('ai_assist_doc_id') == doc_id:
                with st.container(border=True):
                    st.subheader("AI Assistant")
                    default_prompt = requests.get(f"{API_URL}/prompts/new_document/{doc_type}").json()['prompt'].replace("[configurable_item]", project_description)
                    
                    btn_cols = st.columns(2)
                    with btn_cols[0]:
                        if st.button("Generate with AI", key=f"editor_ai_gen_{doc_id}", use_container_width=True):
                            clean_context = re.sub('<[^<]+?>', '', content_data.get("content", ""))
                            full_prompt = f"Context:\n{clean_context}\n\nTask: {st.session_state[f'editor_ai_prompt_{doc_id}']}"
                            with st.spinner("Generating AI content..."):
                                ai_response = requests.post(f"{API_URL}/generate_text", json={"prompt": full_prompt}).json()['text']
                                st.session_state[f'ai_response_{doc_id}'] = ai_response
                                # utils.show_logs(full_prompt, ai_response)
                    with btn_cols[1]:
                        if st.button("Close Assistant", key=f"close_ai_{doc_id}", use_container_width=True):
                            if f'ai_response_{doc_id}' in st.session_state:
                                del st.session_state[f'ai_response_{doc_id}']
                            del st.session_state.ai_assist_doc_id
                            st.rerun()

                    st.text_area("Your prompt to the AI:", value=default_prompt, height=100, key=f"editor_ai_prompt_{doc_id}")
                    
                    if f'ai_response_{doc_id}' in st.session_state:
                        st.text_area("Response from the AI:", value=st.session_state[f'ai_response_{doc_id}'], height=300, key=f"editor_ai_response_{doc_id}")

            # --- Document Editor ---
            st.write("#### Document Content")
            content_from_editor = st_quill(value=content_data.get("content", ""), html=True, key=f"quill_editor_{doc_id}")
            
            # --- Workflow Controls ---
            st.write("#### Document Workflow")
            next_state = st.selectbox("Select Next State", ["Draft", "Review Request", "Approved"], key=f"doc_next_state_{doc_id}")
            
            author_comment = ""
            reviewers = []

            if next_state == "Review Request":
                author_comment = st.text_input("Author comment to Reviewer", key=f"author_comment_{doc_id}")
                users = requests.get(f"{API_URL}/users").json()
                user_map = {u['username']: u['id'] for u in users}
                reviewers = st.multiselect("Select Reviewers", list(user_map.keys()), key=f"reviewers_select_{doc_id}")
            elif next_state == "Approved":
                author_comment = st.text_input("Approval Comments", key=f"approval_comment_{doc_id}")

            if st.button("Save Document Changes", key=f"save_doc_{doc_id}"):
                if next_state == "Review Request":
                    if not reviewers:
                        st.error("Please select at least one reviewer.")
                    elif not author_comment:
                        st.error("Please provide a comment for the reviewer.")
                    else:
                        requests.put(f"{API_URL}/documents/{doc_id}", json={"content": content_from_editor, "status": "Review Request", "comments": author_comment, "author_id": st.session_state['user_info'][0]})
                        for reviewer_name in reviewers:
                            reviewer_id = user_map[reviewer_name]
                            requests.post(f"{API_URL}/reviews", json={"document_id": doc_id, "reviewer_id": reviewer_id, "status": "Pending", "comments": ""})
                        st.success("Document saved and review requested!")
                        st.rerun()
                elif next_state == "Approved":
                    if not author_comment:
                        st.error("Please provide approval comments.")
                    else:
                        requests.put(f"{API_URL}/documents/{doc_id}", json={"content": content_from_editor, "status": "Approved", "comments": author_comment, "author_id": st.session_state['user_info'][0]})
                        st.success("Document approved successfully!")
                        st.rerun()
                else: # Draft
                    requests.put(f"{API_URL}/documents/{doc_id}", json={"content": content_from_editor, "status": "Draft", "comments": author_comment, "author_id": st.session_state['user_info'][0]})
                    st.success("Document saved as draft!")
                    st.rerun()

            st.markdown("---")
            
            # --- Revision History ---
            with st.expander("View Revision History"):
                history = requests.get(f"{API_URL}/documents/{doc_id}/history").json()
                if history:
                    df = pd.DataFrame(history, columns=['id', 'doc_id', 'status', 'author_id', 'timestamp', 'comments'])
                    st.dataframe(df[['status', 'author_id', 'timestamp', 'comments']], use_container_width=True)
                else:
                    st.info("No revision history for this document yet.")

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
                    requests.post(f"{API_URL}/documents", json={"project_id": project_id, "doc_name": item_name, "doc_type": "Code Review", "content": content, "status": "Review Request"})
                    st.success(f"Code Review Item '{item_name}' created successfully!")
                    st.rerun()
                else:
                    st.error("Please provide a name for the review item.")

    st.markdown("---")
    st.subheader("View Existing Code Reviews")
    docs = requests.get(f"{API_URL}/documents/project/{project_id}").json()
    code_reviews = [d for d in docs if d['doc_type'] == "Code Review"]
    if not code_reviews:
        st.info("No code review items in this project yet.")
    else:
        review_item_names = [d['doc_name'] for d in code_reviews]
        selected_item_name = st.selectbox("Select a code review item to view diff", review_item_names)
        if selected_item_name:
            selected_item = next((d for d in code_reviews if d['doc_name'] == selected_item_name), None)
            if selected_item:
                content_data = json.loads(selected_item['content'])
                payload = content_data.get("content", {})
                if isinstance(payload, str):
                    try:
                        payload = json.loads(payload)
                    except Exception:
                        payload = {}
                
                raw_diff = payload.get("raw_diff")
                if raw_diff:
                    diff_html = requests.post(f"{API_URL}/colorize_diff", json={"diff_text": raw_diff}).json()['html']
                    st.components.v1.html(diff_html, height=600, scrolling=True)
                else:  # Fallback for old format
                    code1 = payload.get("code1", "")
                    code2 = payload.get("code2", "")
                    d = difflib.HtmlDiff()
                    diff_html = d.make_table(code1.splitlines(), code2.splitlines())
                    st.components.v1.html(diff_html, height=600, scrolling=True)

                # Add Your Review section
                st.subheader("Add Your Review")
                with st.form(key=f"code_review_form_{selected_item['id']}", clear_on_submit=True):
                    comment = st.text_area("Comments")
                    status = st.selectbox("Status", ["Comment", "Approved", "Needs Revision"])
                    submit_review = st.form_submit_button("Submit Review")
                    if submit_review:
                        requests.post(f"{API_URL}/reviews", json={"document_id": selected_item['id'], "reviewer_id": st.session_state['user_info'][0], "comments": comment, "status": status})
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
            selected_category = st.selectbox("Select a Document Type", requests.get(f"{API_URL}/document_types").json(), key="template_ai_cat")
            prompt_template = requests.get(f"{API_URL}/prompts/new_document/{selected_category}").json()['prompt']
            default_prompt = prompt_template.replace("[configurable_item]", "[describe your item here]")
            
            user_prompt = st.text_area("Your prompt to the AI:", value=default_prompt, height=100, key="template_ai_prompt")

            if st.button("Generate with AI", key="template_ai_gen"):
                with st.spinner("Generating AI content..."):
                    ai_generated_template = requests.post(f"{API_URL}/generate_text", json={"prompt": user_prompt}).json()['text']
                    st.session_state.new_template_quill = ai_generated_template
                    st.session_state.ai_template_open = False
                    # utils.show_logs(user_prompt, ai_generated_template)
                    st.rerun()

    with st.expander("Create New Template", expanded=True):
        with st.form("new_template_form", clear_on_submit=True):
            template_name = st.text_input("Template Name")
            doc_type = st.selectbox("Document Type", requests.get(f"{API_URL}/document_types").json())
            template_content = st_quill(key="new_template_quill", html=True, value=st.session_state.get('new_template_quill', ''))
            submitted = st.form_submit_button("Save New Template")
            if submitted:
                if template_name and doc_type:
                    requests.post(f"{API_URL}/templates", json={"name": template_name, "content": template_content, "document_type": doc_type})
                    if 'new_template_quill' in st.session_state:
                         del st.session_state.new_template_quill
                    st.success(f"Template '{template_name}' created!")
                    st.rerun()
                else:
                    st.error("Template Name and Document Type are required.")

    st.markdown("---")
    st.subheader("Existing Templates")
    templates = requests.get(f"{API_URL}/templates").json()
    if not templates:
        st.info("No templates created yet.")
    else:
        template_names = [t['name'] for t in templates]
        selected_template_name = st.selectbox("Select a template to edit", template_names)
        selected_template = next((t for t in templates if t['name'] == selected_template_name), None)
        if selected_template:
            st.write(f"Editing: **{selected_template['name']}** | Type: {selected_template['document_type']} | Created: {selected_template['created_at']}")
            edited_content = st_quill(value=selected_template['content'], key=f"edit_template_{selected_template['id']}")
            if st.button("Update Template", key=f"update_template_btn_{selected_template['id']}"):
                requests.put(f"{API_URL}/templates/{selected_template['id']}", json={"name": selected_template['name'], "content": edited_content})
                st.success("Template updated successfully!")
                st.rerun()

def knowledge_base_page():
    st.header("Knowledge Base Management")

    # --- Knowledge Base Status ---
    kb_status_response = requests.get(f"{API_URL}/knowledge_base/status").json()
    if kb_status_response.get("status") == "ready":
        st.success("Knowledge Base is Ready")
    else:
        st.warning("Knowledge Base is not Ready. Please upload documents to create it.")

    # --- File and URL Uploader ---
    st.subheader("Upload Content")
    uploaded_files = st.file_uploader(
        "Upload PDF or Markdown files", 
        accept_multiple_files=True, 
        type=['pdf', 'md', 'txt']
    )
    urls_input = st.text_area("Enter URLs to scrape (one per line)")

    if st.button("Create/Update Knowledge Base"):
        if not uploaded_files and not urls_input:
            st.warning("Please upload files or enter URLs to update the knowledge base.")
        else:
            with st.spinner("Processing content and updating knowledge base..."):
                # 1. Upload files
                if uploaded_files:
                    files_to_upload = [("files", (file.name, file.getvalue(), file.type)) for file in uploaded_files]
                    upload_response = requests.post(f"{API_URL}/knowledge_base/upload", files=files_to_upload)
                    if upload_response.status_code != 200:
                        st.error(f"Error uploading files: {upload_response.text}")
                        return

                # 2. Trigger update
                urls = [url.strip() for url in urls_input.split('\n') if url.strip()]
                update_response = requests.post(f"{API_URL}/knowledge_base/update", json={"urls": urls})
                
                if update_response.status_code == 200:
                    st.success(update_response.json()['message'])
                else:
                    st.error(f"Error updating knowledge base: {update_response.text}")

    # --- Reset Knowledge Base ---
    st.subheader("Manage Knowledge Base")
    if st.button("Reset Knowledge Base"):
        with st.spinner("Resetting knowledge base..."):
            response = requests.post(f"{API_URL}/knowledge_base/reset")
            if response.status_code == 200:
                st.success("Knowledge base reset successfully!")
                st.rerun()
            else:
                st.error(f"Error resetting knowledge base: {response.text}")

    st.markdown("---")

    # --- CHAT INTERFACE ---
    st.subheader("Chat with your Knowledge Base")

    if 'chat_history' not in st.session_state:
        st.session_state.chat_history = []

    for message in st.session_state.chat_history:
        with st.chat_message(message["role"]):
            st.markdown(message["content"])

    if prompt := st.chat_input("Ask a question to the knowledge base"):
        st.session_state.chat_history.append({"role": "user", "content": prompt})
        with st.chat_message("user"):
            st.markdown(prompt)

        with st.chat_message("assistant"):
            with st.spinner("Thinking..."):
                response = requests.post(f"{API_URL}/knowledge_base/query", json={"query": prompt})
                if response.status_code == 200:
                    st.markdown(response.json()['answer'])
                    st.session_state.chat_history.append({"role": "assistant", "content": response.json()['answer']})
                else:
                    st.error(f"Error querying knowledge base: {response.text}")

def team_tab(project_id):
    st.subheader("Team Members")
    
    with st.expander("Add New Team Member"):
        with st.form("new_team_member_form", clear_on_submit=True):
            users = requests.get(f"{API_URL}/users").json()
            user_map = {u['username']: u['id'] for u in users}
            selected_user = st.selectbox("Select User", list(user_map.keys()))
            submitted = st.form_submit_button("Add Member")
            if submitted:
                user_id = user_map[selected_user]
                requests.post(f"{API_URL}/team_members", json={"user_id": user_id, "project_id": project_id})
                st.success("Team member added!")
                st.rerun()

    team_members = requests.get(f"{API_URL}/team_members/{project_id}").json()
    if team_members:
        st.write("#### Current Team Members")
        df = pd.DataFrame(team_members)
        
        for i, row in df.iterrows():
            col1, col2, col3, col4 = st.columns([0.5, 2, 2, 1])
            with col1:
                st.write(row['id'])
            with col2:
                st.write(row['username'])
            with col3:
                st.write(row['email'])
            with col4:
                if st.button("Delete", key=f"delete_team_member_{row['id']}_{project_id}"):
                    requests.delete(f"{API_URL}/team_members/{project_id}/{row['id']}")
                    st.success(f"Team member {row['username']} deleted.")
                    st.rerun()
    else:
        st.info("No team members in this project yet.")

def traceability_tab(project_id):
    st.subheader("Traceability Matrix")
    traceability_links = requests.get(f"{API_URL}/traceability/{project_id}").json()
    if traceability_links:
        df = pd.DataFrame(traceability_links)
        st.dataframe(df[['requirement_ref', 'design_ref', 'test_ref']])
    else:
        st.info("No traceability links recorded for this project yet.")

    with st.expander("Add New Traceability"):
        with st.form("new_traceability_form", clear_on_submit=True):
            req_ref = st.text_input("Requirement Reference")
            design_ref = st.text_input("Design Reference")
            test_ref = st.text_input("Test Reference")
            submitted = st.form_submit_button("Add Trace")
            if submitted:
                requests.post(f"{API_URL}/traceability", json={"project_id": project_id, "requirement_ref": req_ref, "design_ref": design_ref, "test_ref": test_ref})
                st.success("Traceability link added!")
                st.rerun()

def hazard_traceability_tab(project_id):
    st.subheader("Hazards")
    hazard_assessment_type = st.selectbox("Select Hazard Assessment Type", ["Medical (IEC 62304)", "Automotive (ISO 26262)", "General (IEC 61508)"])

    if hazard_assessment_type == "Medical (IEC 62304)":
        hazard_links = requests.get(f"{API_URL}/hazards/medical/{project_id}").json()
        if hazard_links:
            df = pd.DataFrame(hazard_links)
            st.data_editor(df[['hazard', 'cause', 'effect', 'risk_control_measure', 'verification', 'severity', 'occurrence', 'detection', 'mitigation_notes']])
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
                    requests.post(f"{API_URL}/hazards/medical", json={"project_id": project_id, "hazard": hazard, "cause": cause, "effect": effect, "risk_control_measure": risk_control, "verification": verification, "severity": sev, "occurrence": occ, "detection": det, "mitigation_notes": mitigation_notes})
                    st.success("Hazard traceability link added!")
                    st.rerun()
    
    elif hazard_assessment_type == "Automotive (ISO 26262)":
        st.subheader("ASIL Determination")
        asil_entries = requests.get(f"{API_URL}/hazards/automotive/{project_id}").json()
        if asil_entries:
            df = pd.DataFrame(asil_entries)
            st.dataframe(df[['hazard_description', 'severity', 'exposure', 'controllability', 'asil_rating']])
        else:
            st.info("No ASIL entries recorded for this project yet.")

        with st.expander("Add New ASIL Entry"):
            with st.form("new_asil_form", clear_on_submit=True):
                hazard_desc = st.text_input("Hazard Description")
                sev_options = requests.get(f"{API_URL}/options/hazards/automotive/severity").json()
                sev = st.selectbox("Severity", options=sev_options)
                exp_options = requests.get(f"{API_URL}/options/hazards/automotive/exposure").json()
                exp = st.selectbox("Exposure", options=exp_options)
                con_options = requests.get(f"{API_URL}/options/hazards/automotive/controllability").json()
                con = st.selectbox("Controllability", options=con_options)
                submitted = st.form_submit_button("Calculate and Add ASIL")
                if submitted:
                    response = requests.post(f"{API_URL}/hazards/automotive", json={"project_id": project_id, "hazard_description": hazard_desc, "severity": sev, "exposure": exp, "controllability": con})
                    if response.status_code == 200 and 'application/json' in response.headers.get('Content-Type', ''):
                        asil_rating = response.json().get('asil_rating')
                        if asil_rating:
                            st.success(f"ASIL entry added with rating: {asil_rating}!")
                        else:
                            st.error("Failed to retrieve ASIL rating from response.")
                    else:
                        st.error(f"Failed to add ASIL entry. Status: {response.status_code}, Response: {response.text}")
                    st.rerun()

    elif hazard_assessment_type == "General (IEC 61508)":
        st.subheader("SIL Determination")
        sil_entries = requests.get(f"{API_URL}/hazards/general/{project_id}").json()
        if sil_entries:
            df = pd.DataFrame(sil_entries)
            st.dataframe(df[['hazard_description', 'consequence', 'exposure', 'avoidance', 'probability', 'sil_rating']])
        else:
            st.info("No SIL entries recorded for this project yet.")

        with st.expander("Add New SIL Entry"):
            with st.form("new_sil_form", clear_on_submit=True):
                hazard_desc = st.text_input("Hazard Description")
                cons_options = requests.get(f"{API_URL}/options/hazards/general/consequence").json()
                cons = st.selectbox("Consequence", options=cons_options)
                exp_options = requests.get(f"{API_URL}/options/hazards/general/exposure").json()
                exp = st.selectbox("Exposure", options=exp_options)
                avo_options = requests.get(f"{API_URL}/options/hazards/general/avoidance").json()
                avo = st.selectbox("Avoidance", options=avo_options)
                prob_options = requests.get(f"{API_URL}/options/hazards/general/probability").json()
                prob = st.selectbox("Probability", options=prob_options)
                submitted = st.form_submit_button("Calculate and Add SIL")
                if submitted:
                    response = requests.post(f"{API_URL}/hazards/general", json={"project_id": project_id, "hazard_description": hazard_desc, "consequence": cons, "exposure": exp, "avoidance": avo, "probability": prob})
                    if response.status_code == 200 and 'application/json' in response.headers.get('Content-Type', ''):
                        sil_rating = response.json().get('sil_rating')
                        if sil_rating:
                            st.success(f"SIL entry added with rating: {sil_rating}!")
                        else:
                            st.error("Failed to retrieve SIL rating from response.")
                    else:
                        st.error(f"Failed to add SIL entry. Status: {response.status_code}, Response: {response.text}")
                    st.rerun()

def artifacts_tab(project_id):
    st.subheader("Approved Documents")
    
    docs = requests.get(f"{API_URL}/documents/project/{project_id}?status=Approved").json()

    if not docs:
        st.info("No approved documents yet.")
    else:
        df = pd.DataFrame(docs)
        st.dataframe(df[['doc_name', 'doc_type', 'version', 'updated_at']])

        if st.button("Download All"):
            zip_buffer = requests.get(f"{API_URL}/documents/project/{project_id}/download_all").content
            st.download_button(
                label="Download All as ZIP",
                data=zip_buffer,
                file_name=f"Project_{project_id}_Documents_{datetime.now().strftime('%Y%m%d')}.zip",
                mime="application/zip"
            )

def audit_tab(project_id):
    st.subheader("Audit")

    if st.button("AI Assist"):
        st.info("Audit Run with AI...")
        response = requests.post(f"{API_URL}/audit/{project_id}")
        if response.status_code == 200:
            st.success("AI Audit complete!")
        else:
            st.error(f"Error starting audit: {response.text}")

    # Display audit gaps
    st.subheader("Audit Gaps")
    
    audit_references = requests.get(f"{API_URL}/audit/references/{project_id}").json()
    if audit_references:
        selected_audit_reference = st.selectbox("Select Audit Reference", audit_references)

        if selected_audit_reference:
            audit_gaps_data = requests.get(f"{API_URL}/audit/gaps/{project_id}/{selected_audit_reference}").json()
            if audit_gaps_data:
                audit_timestamp = audit_gaps_data[0]['audit_timestamp']
                st.write(f"**Audit Run Date/Time:** {audit_timestamp}")

                df = pd.DataFrame(audit_gaps_data)
                
                markdown_report = requests.get(f"{API_URL}/audit/report/{project_id}/{selected_audit_reference}").text
                st.download_button(
                    label="Download Report",
                    data=markdown_report,
                    file_name=f"{selected_audit_reference}.md",
                    mime='text/markdown',
                )

                edited_df = st.data_editor(
                    df[['document_name', 'comments', 'status']], 
                    column_config={
                        "status": st.column_config.SelectboxColumn(
                            "Status",
                            options=["New", "In Progress", "Done"],
                            required=True,
                        )
                    },
                    use_container_width=True
                )
    else:
        st.info("No audit gaps found.")

def training_tab(project_id):
    st.subheader("Training")

    if st.button("AI Assist", key=f"training_ai_assist_{project_id}"):
        st.session_state.ai_training_assist = True

    if st.session_state.get('ai_training_assist'):
        with st.expander("AI Training Assist", expanded=True):
            default_prompt = requests.get(f"{API_URL}/prompts/training").json()['prompt']
            user_prompt = st.text_area("Review and edit the prompt for generating questions:", value=default_prompt, height=150, key=f"training_prompt_editor_{project_id}")

            if st.button("Generate Questions", key=f"generate_training_questions_{project_id}"):
                with st.spinner("Generating training questions..."):
                    response = requests.post(f"{API_URL}/training/generate", json={"project_id": project_id, "user_id": st.session_state['user_info'][0], "prompt": user_prompt})
                    if response.status_code == 200:
                        st.session_state.ai_training_assist = False
                        st.rerun()
                    else:
                        st.error(f"Error generating questions: {response.text}")

    questions_to_answer = requests.get(f"{API_URL}/training/questions/{project_id}/{st.session_state['user_info'][0]}").json()
    if questions_to_answer:
        with st.form(key=f"training_form_{project_id}"):
            answers = {}
            for q in questions_to_answer:
                answers[q['id']] = st.radio(q['question'], ["True", "False"], key=f"q_{q['id']}")
            
            submitted = st.form_submit_button("Submit Answers")
            if submitted:
                for q_id, answer in answers.items():
                    requests.put(f"{API_URL}/training/answer/{q_id}", json={"user_answer": answer})
                st.rerun()

    st.subheader("Training History")
    training_history = requests.get(f"{API_URL}/training/history/{project_id}/{st.session_state['user_info'][0]}").json()
    if training_history:
        df = pd.DataFrame(training_history)
        df['correct'] = df['user_answer'] == df['actual_answer']
        st.dataframe(df[['timestamp', 'question', 'user_answer', 'actual_answer', 'correct']])
        
        total_correct = df['correct'].sum()
        total_questions = len(df)
        st.metric("Overall Score", f"{total_correct}/{total_questions} ({total_correct/total_questions:.2%})")
    else:
        st.info("No training history yet.")

def reviews_tab(project_id):
    st.subheader("Reviews")
    review_choice = st.radio("Select Review Type", ["My Reviews", "All Reviews"])

    if review_choice == "My Reviews":
        reviews = requests.get(f"{API_URL}/reviews/user/{st.session_state['user_info'][0]}").json()
        if not reviews:
            st.info("You have no documents to review.")
        else:
            with st.expander("Review Items", expanded=True):
                review_options = {f"{r['doc_name']} (v{r['version']}) - Status: {r['status']}" : r['document_id'] for r in reviews}
                selected_review_label = st.selectbox("Select a review to comment on:", list(review_options.keys()))

                if selected_review_label:
                    doc_id = review_options[selected_review_label]
                    doc_details = requests.get(f"{API_URL}/documents/{doc_id}").json()
                    if doc_details:
                        if doc_details['doc_type'] == "Code Review": # Check if it's a code review
                            content_data = json.loads(doc_details['content'])
                            payload = content_data.get("content", {})
                            if isinstance(payload, str):
                                try:
                                    payload = json.loads(payload)
                                except:
                                    payload = {}
                            
                            raw_diff = payload.get("raw_diff")
                            if raw_diff:
                                diff_html = requests.post(f"{API_URL}/colorize_diff", json={"diff_text": raw_diff}).json()['html']
                                st.components.v1.html(diff_html, height=600, scrolling=True)
                            else: # Fallback for old format
                                code1 = payload.get("code1", "")
                                code2 = payload.get("code2", "")
                                d = difflib.HtmlDiff()
                                diff_html = d.make_table(code1.splitlines(), code2.splitlines())
                                st.components.v1.html(diff_html, height=600, scrolling=True)
                        else:
                            content_data = json.loads(doc_details['content'])
                            st.markdown(content_data.get("content", ""), unsafe_allow_html=True)

            if selected_review_label:
                st.subheader("Add Your Review")
                with st.form(key="review_form", clear_on_submit=True):
                    comment = st.text_area("Comments")
                    new_status = st.selectbox("Status", ["Comment", "Approved", "Needs Revision"])
                    submit_review = st.form_submit_button("Submit Review")
                    if submit_review:
                        requests.post(f"{API_URL}/reviews", json={"document_id": doc_id, "reviewer_id": st.session_state['user_info'][0], "comments": comment, "status": new_status})
                        st.success("Your review has been submitted.")
                        st.rerun()

    elif review_choice == "All Reviews":
        all_reviews = requests.get(f"{API_URL}/reviews/project/{project_id}").json()
        if not all_reviews:
            st.info("No reviews for this project yet.")
        else:
            df = pd.DataFrame(all_reviews)
            st.dataframe(df)

def admin_page():
    st.header("Admin Dashboard")
    st.subheader("Manage Users")
    users = requests.get(f"{API_URL}/users").json()
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

    To begin, you need to sign up with a unique username and email. Once logged in, you will be directed to your **Dashboard**.

    ### Dashboard
    The Dashboard provides a personalized overview of your activities, including:
    *   **Quick Stats:** Total accessible projects, documents awaiting your review, and pending training questions.
    *   **My Action Items:** Direct links to documents requiring your review and pending training sessions.
    *   **My Projects Overview:** A summary of all projects you are involved in.

    From the sidebar, you can navigate to different sections of the application.

    ## Key Features

    ### Projects

    *   **Create New Project**: Use the form in the sidebar to create a new project.
    *   **Edit Project**: Select a project and use the "Edit Current Project" section to update its name and description.
    *   **Team**: Manage team members for a project. You can **add new members** and **delete existing ones** from the project team.
    *   **Documents**: Manage all your project documents. You can create new documents from scratch, templates, or existing documents. The editor allows for rich-text editing, and you can export documents to PDF. Newly created documents are automatically selected for immediate editing.
    *   **Code**: A dedicated space for code reviews with a diff viewer.
    *   **Reviews**: A dedicated space for document and code reviews.
        *   **My Reviews**: View items assigned to you for review. Select an item to see the content and add your comments.
        *   **All Reviews**: See a complete history of all reviews for the project.
    *   **Hazards**: A combined table for hazard analysis and risk management, tailored to different standards like Medical (IEC 62304), Automotive (ISO 26262), and General (IEC 61508).
    *   **Traceability**: Manage traceability between requirements, design, and tests.
    *   **Approved**: Download all approved documents as a single zip file.
    *   **AI Audit**: Audit trail for AI-assisted actions.
    *   **Training**: Generate True/False questions from the knowledge base to test your understanding of SOPs. Track your scores and review your answers.

    ### Review Items (Documents and Code)

    *   **Create New Review Item**: You can create a new document from scratch, from a template, from an existing document, or with the help of AI. You can also create code review items in the "Code" tab.
    *   **Editor**: Use the rich-text editor to create and format your documents. While the editor does not have built-in table creation controls, you can create tables using external tools and paste their HTML or Markdown syntax directly into the editor. For example, you can use online Markdown table generators to create the table structure, then copy and paste the raw Markdown into the editor. The editor will render basic HTML tables.
    *   **AI Assist**: Get help from the AI to improve your writing, summarize content, or generate new ideas.
    *   **Request Review**: When your item is ready, you can request a review from team members.
    *   **Revision History**: Track all changes made to an item over time.
    *   **Export to PDF**: Download a PDF version of your document for easy sharing and archiving.

    ### Templates

    *   **Create and Manage Templates**: Create reusable document templates to standardize your documentation process.
    *   **AI Template Generation**: Use the AI to generate new templates based on your requirements.

    ### Knowledge Base

    *   **Upload Content**: Upload your own documents (PDF, Markdown, or text files) and/or scrape websites to create or update a local knowledge base for the RAG system.
    *   **Chat**: Chat with your knowledge base to get answers to your questions.
    *   **Status**: The status of the knowledge base is displayed at the top of the page. If it's not ready, you need to upload documents to create it.
    *   **Reset**: You can reset the knowledge base, which will delete all the existing data.

    ### Admin

    *   **User Management**: Admins can view and manage all users in the system.
    """)
