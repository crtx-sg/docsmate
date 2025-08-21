import streamlit as st
import pandas as pd
import json
import os
from datetime import datetime
import zipfile
import re

import config
import ai_integration
import knowledge_base
import database
import utils
from streamlit_quill import st_quill

def dashboard_page():
    st.header(f"Welcome, {st.session_state['user_info'][1]}!")

    user_id = st.session_state['user_info'][0]

    # --- Quick Stats ---
    st.subheader("Quick Stats")
    col1, col2, col3, col4 = st.columns(4)

    accessible_projects = database.get_user_accessible_projects(user_id)
    with col1:
        st.metric("Total Projects Accessible", len(accessible_projects))

    pending_reviews = database.get_reviews_for_user(user_id)
    pending_review_count = sum(1 for r in pending_reviews if r[3] == 'Pending')
    with col2:
        st.metric("Documents Awaiting My Review", pending_review_count)

    pending_training_questions = database.get_training_questions(user_id, user_id) # Assuming user_id is also project_id for simplicity or adjust as needed
    with col3:
        st.metric("Pending Training Questions", len(pending_training_questions))
    
    training_history = database.get_training_history(user_id, user_id)
    if training_history:
        df_training = pd.DataFrame(training_history, columns=['ID', 'Project ID', 'Question', 'User Answer', 'Actual Answer', 'User ID', 'Timestamp'])
        total_correct = df_training['User Answer'].eq(df_training['Actual Answer']).sum()
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
            if review[3] == 'Pending':
                doc_details = database.get_document_details(review[0])
                if doc_details:
                    st.info(f"**Project:** {review[2]} - **Document:** {doc_details[2]} (Status: {review[3]})")
                    # Provide a way to navigate to the review, perhaps by setting session state
                    # This would require a more complex navigation handling or direct link if possible
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
        project_data = []
        for p in accessible_projects:
            project_id, name, description, created_by_user_id = p
            creator_name = database.get_user_by_id(created_by_user_id)
            project_data.append({
                "ID": project_id,
                "Name": name,
                "Description": description,
                "Created By": creator_name
            })
        df_projects = pd.DataFrame(project_data)
        st.dataframe(df_projects, use_container_width=True)
    else:
        st.info("You are not associated with any projects yet. Create a new project from the sidebar.")

    st.markdown("---")

    # --- My Recent Document Activity ---
    st.subheader("My Recent Document Activity")
    # This would require fetching recent document updates related to the user's projects
    # For simplicity, let's just show a placeholder or a general message for now
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
                database.create_project(project_name, project_desc, st.session_state['user_info'][0])
                st.success("Project created successfully!")
                st.rerun()

    projects = database.get_user_accessible_projects(st.session_state['user_info'][0])
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
                        database.update_project(selected_project[0], new_project_name, new_project_desc)
                        st.success("Project updated successfully!")
                        st.rerun()
            
            project_detail_page(selected_project[0])

def project_detail_page(project_id):
    project_details = database.get_project_details(project_id)
    st.header(f"Project: {project_details[1]}")
    st.write(project_details[2])
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
    project_details = database.get_project_details(project_id)
    project_description = project_details[2] if project_details else ""

    # --- Create New Document Section ---
    with st.expander("Create New Document", expanded=False):
        source_options = ["From Scratch", "From a Template", "From an Existing Document"]
        doc_type = None
        content_source = ""
        version = 1
        doc_name_suggestion = ""

        source_choice = st.radio("Create document from:", source_options, key=f"doc_create_source_{project_id}")
        
        if source_choice == "From Scratch":
            doc_type = st.selectbox("Select Document Type", config.DOCUMENT_TYPES, key=f"scratch_type_{project_id}")
        elif source_choice == "From a Template":
            templates = database.get_templates()
            if templates:
                template_map = {t[1]: (t[2], t[3]) for t in templates}
                selected_template_name = st.selectbox("Select a template", options=list(template_map.keys()), key=f"template_select_{project_id}")
                content_source, doc_type = template_map[selected_template_name]
                doc_name_suggestion = selected_template_name
            else:
                st.warning("No templates found.")
        elif source_choice == "From an Existing Document":
            existing_docs = database.get_documents(project_id)
            if existing_docs:
                doc_map = {f"{d[2]}(v{d[5]})": (json.loads(d[4]).get("content", ""), d[3], d[5], d[2]) for d in existing_docs}
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
                doc_id = database.create_document(project_id, item_name, doc_type, content_source, version)
                st.session_state.last_created_doc_id = doc_id
                if 'ai_generated_content' in st.session_state:
                    del st.session_state.ai_generated_content
                st.success(f"Document '{item_name}' created successfully!")
                st.rerun()
            else:
                st.error("Document Name and Type are required.")

    st.markdown("---")
    
    # --- Existing Documents Selection ---
    docs = database.get_documents(project_id)
    if not docs:
        st.info("No documents in this project yet. Create one above to get started.")
        return # Exit function if no documents to display/edit

    doc_display_names = [f"{d[2]}(v{d[5]})" for d in docs]
    doc_ids = [d[0] for d in docs]

    default_index = 0
    if 'last_created_doc_id' in st.session_state and st.session_state.last_created_doc_id in doc_ids:
        default_index = doc_ids.index(st.session_state.last_created_doc_id)
        # Clear the session state variable after using it
        del st.session_state.last_created_doc_id

    selected_doc_name_display = st.selectbox("Select a document to view or edit", doc_display_names, index=default_index, key=f"doc_select_{project_id}")
    
    if selected_doc_name_display:
        selected_doc_index = doc_display_names.index(selected_doc_name_display)
        selected_doc_info = docs[selected_doc_index]
        doc_details = database.get_document_details(selected_doc_info[0])

        if doc_details:
            doc_id, _, doc_name, doc_type, content_json, version, status, _, updated_at = doc_details
            content_data = json.loads(content_json)
            
            st.markdown(f"### {doc_name} (v{version}) - Status: {status}")

            # --- Document Editor Actions ---
            col_ai, col_pdf = st.columns([0.15, 0.15])
            with col_ai:
                if st.button("✨ AI Assist", key=f"ai_assist_{doc_id}"):
                    st.session_state.ai_assist_doc_id = doc_id if st.session_state.get('ai_assist_doc_id') != doc_id else None
            with col_pdf:
                pdf_data = utils.generate_pdf(content_data.get("content", ""))
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
                    default_prompt = config.NEW_DOCUMENT_PROMPTS.get(doc_type, f"Improve the clarity of this {doc_type} document.").replace("[configurable_item]", project_description)
                    
                    btn_cols = st.columns(2)
                    with btn_cols[0]:
                        if st.button("Generate with AI", key=f"editor_ai_gen_{doc_id}", use_container_width=True):
                            clean_context = re.sub('<[^<]+?>', '', content_data.get("content", ""))
                            full_prompt = f"Context:\n{clean_context}\n\nTask: {st.session_state[f'editor_ai_prompt_{doc_id}']}"
                            with st.spinner("Generating AI content..."):
                                ai_response = ai_integration.generate_text(full_prompt)
                                st.session_state[f'ai_response_{doc_id}'] = ai_response
                                utils.show_logs(full_prompt, ai_response)
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
                users = database.get_all_users()
                user_map = {u[1]: u[0] for u in users}
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
                        database.update_document(doc_id, content_from_editor, "Review Request", author_comment, st.session_state['user_info'][0])
                        for reviewer_name in reviewers:
                            reviewer_id = user_map[reviewer_name]
                            database.add_review(doc_id, reviewer_id, "", "Pending")
                        st.success("Document saved and review requested!")
                        st.rerun()
                elif next_state == "Approved":
                    if not author_comment:
                        st.error("Please provide approval comments.")
                    else:
                        database.update_document(doc_id, content_from_editor, "Approved", author_comment, st.session_state['user_info'][0])
                        st.success("Document approved successfully!")
                        st.rerun()
                else: # Draft
                    database.update_document(doc_id, content_from_editor, "Draft", author_comment, st.session_state['user_info'][0])
                    st.success("Document saved as draft!")
                    st.rerun()

            st.markdown("---")
            
            # --- Revision History ---
            with st.expander("View Revision History"):
                history = database.get_revision_history(doc_id)
                if history:
                    df = pd.DataFrame(history, columns=['ID', 'Doc ID', 'Status', 'Author ID', 'Timestamp', 'Comments'])
                    df['Author'] = df['Author ID'].apply(database.get_user_by_id)
                    st.dataframe(df[['Status', 'Author', 'Timestamp', 'Comments']], use_container_width=True)
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
                    database.create_document(project_id, item_name, "Code Review", content, status="Review Request")
                    st.success(f"Code Review Item '{item_name}' created successfully!")
                    st.rerun()
                else:
                    st.error("Please provide a name for the review item.")

    st.markdown("---")
    st.subheader("View Existing Code Reviews")
    docs = database.get_documents(project_id)
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
                    diff_html = utils.colorize_diff_to_html(raw_diff)
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
                        database.add_review(selected_item[0], st.session_state['user_info'][0], comment, status)
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
                    utils.show_logs(user_prompt, ai_generated_template)
                    st.rerun()

    with st.expander("Create New Template", expanded=True):
        with st.form("new_template_form", clear_on_submit=True):
            template_name = st.text_input("Template Name")
            doc_type = st.selectbox("Document Type", config.DOCUMENT_TYPES)
            template_content = st_quill(key="new_template_quill", html=True, value=st.session_state.get('new_template_quill', ''))
            submitted = st.form_submit_button("Save New Template")
            if submitted:
                if template_name and doc_type:
                    database.create_template(template_name, template_content, doc_type)
                    if 'new_template_quill' in st.session_state:
                         del st.session_state.new_template_quill
                    st.success(f"Template '{template_name}' created!")
                    st.rerun()
                else:
                    st.error("Template Name and Document Type are required.")

    st.markdown("---")
    st.subheader("Existing Templates")
    templates = database.get_templates()
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
                database.update_template(selected_template[0], selected_template[1], edited_content)
                st.success("Template updated successfully!")
                st.rerun()

def knowledge_base_page():
    st.header("Knowledge Base Management")

    # Initialize vector_store in session state if not present
    if 'vector_store' not in st.session_state:
        st.session_state.vector_store = None

    # Attempt to load existing knowledge base on page load
    if st.session_state.vector_store is None:
        try:
            # Try to load an existing collection without adding new data initially
            # This requires a slight modification or a new function in knowledge_base.py
            # For now, we'll assume create_and_store_embeddings can handle loading empty chunks
            # if the collection already exists.
            st.session_state.vector_store = knowledge_base.create_and_store_embeddings(chunks=[])
            st.success("Existing knowledge base loaded.")
        except Exception as e:
            st.info(f"No existing knowledge base found or error loading: {e}. Please create one.")
            st.session_state.vector_store = None

    # --- KNOWLEDGE BASE CREATION / UPDATE ---
    st.subheader("Knowledge Base Content")
    st.write("Upload documents and/or scrape websites to build or update your knowledge base.")
    
    uploaded_files = st.file_uploader("Upload PDFs and Markdown files", accept_multiple_files=True, type=['pdf', 'md'], key="kb_uploader")
    
    if uploaded_files:
        # Save uploaded files to the upload directory
        for uploaded_file in uploaded_files:
            file_path = os.path.join(config.UPLOAD_DIRECTORY, uploaded_file.name)
            with open(file_path, "wb") as f:
                f.write(uploaded_file.getbuffer())
        st.success(f"{len(uploaded_files)} file(s) ready for processing.")

    col1, col2, col3 = st.columns(3)

    with col1:
        if st.button("Create/Update Knowledge Base", key="create_update_kb_btn"):
            with st.spinner("Processing content and updating knowledge base... This may take a while."):
                # 1. Scrape websites
                scraped_text = knowledge_base.scrape_websites(config.WEBSITES_TO_SCRAPE)
                
                # 2. Process uploaded files
                uploaded_text = knowledge_base.process_uploaded_files(config.UPLOAD_DIRECTORY)
                
                # 3. Combine text
                full_text = scraped_text + uploaded_text
                
                # 4. Chunk text
                chunks = knowledge_base.chunk_text(full_text)
                
                # 5. Create/Update and store embeddings
                if st.session_state.vector_store is None:
                    st.session_state.vector_store = knowledge_base.create_and_store_embeddings(chunks)
                    st.success("Knowledge base created successfully!")
                else:
                    st.session_state.vector_store = knowledge_base.update_embeddings(chunks)
                    st.success("Knowledge base updated successfully!")

    with col2:
        if st.button("Reset Knowledge Base", key="reset_kb_btn"):
            if st.session_state.vector_store is not None:
                with st.spinner("Resetting knowledge base..."):
                    knowledge_base.reset_knowledge_base()
                    st.session_state.vector_store = None
                    st.success("Knowledge base reset successfully!")
                    st.rerun()
            else:
                st.info("No knowledge base to reset.")

    st.markdown("---")

    # --- CHAT INTERFACE ---
    st.subheader("Chat with your Knowledge Base")

    if st.session_state.vector_store is None:
        st.warning("Please create or update the knowledge base to enable chat functionality.")
    else:
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
                    # 1. Query the knowledge base
                    docs = knowledge_base.query_knowledge_base(prompt, st.session_state.vector_store)
                    
                    # 2. Get answer from LLM
                    response = knowledge_base.get_answer_from_llm(prompt, docs)
                    
                    st.markdown(response)
            
            st.session_state.chat_history.append({"role": "assistant", "content": response})

def team_tab(project_id):
    st.subheader("Team Members")
    
    with st.expander("Add New Team Member"):
        with st.form("new_team_member_form", clear_on_submit=True):
            users = database.get_all_users()
            user_map = {u[1]: u[0] for u in users}
            selected_user = st.selectbox("Select User", list(user_map.keys()))
            submitted = st.form_submit_button("Add Member")
            if submitted:
                user_id = user_map[selected_user]
                database.add_team_member(user_id, project_id)
                st.success("Team member added!")
                st.rerun()

    team_members = database.get_team_members(project_id)
    if team_members:
        st.write("#### Current Team Members")
        df = pd.DataFrame(team_members, columns=['ID', 'Username', 'Email'])
        
        # Add a 'Delete' column with buttons
        for i, row in df.iterrows():
            col1, col2, col3, col4 = st.columns([0.5, 2, 2, 1])
            with col1:
                st.write(row['ID'])
            with col2:
                st.write(row['Username'])
            with col3:
                st.write(row['Email'])
            with col4:
                if st.button("Delete", key=f"delete_team_member_{row['ID']}_{project_id}"):
                    database.delete_team_member(row['ID'], project_id)
                    st.success(f"Team member {row['Username']} deleted.")
                    st.rerun()
    else:
        st.info("No team members in this project yet.")


def traceability_tab(project_id):
    st.subheader("Traceability Matrix")
    traceability_links = database.get_traceability(project_id)
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
                database.add_traceability(project_id, req_ref, design_ref, test_ref)
                st.success("Traceability link added!")
                st.rerun()

def hazard_traceability_tab(project_id):
    st.subheader("Hazards")
    hazard_assessment_type = st.selectbox("Select Hazard Assessment Type", ["Medical (IEC 62304)", "Automotive (ISO 26262)", "General (IEC 61508)"])

    if hazard_assessment_type == "Medical (IEC 62304)":
        hazard_links = database.get_hazard_traceability(project_id)
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
                    database.add_hazard_traceability(project_id, hazard, cause, effect, risk_control, verification, sev, occ, det, mitigation_notes)
                    st.success("Hazard traceability link added!")
                    st.rerun()
    
    elif hazard_assessment_type == "Automotive (ISO 26262)":
        st.subheader("ASIL Determination")
        asil_entries = database.get_asil_entries(project_id)
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
                    database.add_asil_entry(project_id, hazard_desc, sev, exp, con, asil_rating)
                    st.success("ASIL entry added!")
                    st.rerun()

    elif hazard_assessment_type == "General (IEC 61508)":
        st.subheader("SIL Determination")
        sil_entries = database.get_sil_entries(project_id)
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
                    database.add_sil_entry(project_id, hazard_desc, cons, exp, avo, prob, sil_rating)
                    st.success("SIL entry added!")
                    st.rerun()

def artifacts_tab(project_id):
    st.subheader("Approved Documents")
    
    docs = database.get_documents(project_id)
    approved_docs = [d for d in docs if d[6] == "Approved"]

    if not approved_docs:
        st.info("No approved documents yet.")
    else:
        df = pd.DataFrame(approved_docs, columns=['id', 'project_id', 'doc_name', 'doc_type', 'content', 'version', 'status', 'created_at', 'updated_at'])
        df['author'] = database.get_user_by_id(database.get_project_details(project_id)[3])
        st.dataframe(df[['doc_name', 'doc_type', 'version', 'author', 'updated_at']])

        if st.button("Download All"):
            zip_buffer = BytesIO()
            with zipfile.ZipFile(zip_buffer, "a", zipfile.ZIP_DEFLATED, False) as zip_file:
                added, skipped = 0, 0
                for index, row in df.iterrows():
                    pdf_data = utils.generate_pdf(json.loads(row['content']).get("content", ""))
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
                file_name=f"{database.get_project_details(project_id)[1]}_Documents_{datetime.now().strftime('%Y%m%d')}.zip",
                mime="application/zip"
            )

def audit_tab(project_id):
    st.subheader("Audit")

    project_details = database.get_project_details(project_id)
    project_name = project_details[1] if project_details else "Unknown Project"

    if st.button("AI Assist"):
        st.info("Starting AI Audit...")

        # Create a unique audit reference number
        audit_timestamp = datetime.now()
        audit_reference = f"{project_name}-Audit-{audit_timestamp.strftime('%Y%m%d%H%M%S')}"

        # Get approved documents
        with st.spinner("Fetching approved documents..."):
            docs = database.get_documents(project_id)
            approved_docs = [d for d in docs if d[6] == "Approved"]
            st.success(f"Found {len(approved_docs)} approved documents.")

        if not approved_docs:
            st.warning("No approved documents to audit.")
            return

        for doc in approved_docs:
            doc_id, _, doc_name, doc_type, content_json, _, _, _, _ = doc
            content_data = json.loads(content_json)
            document_content = content_data.get("content", "")

            with st.spinner(f"Auditing document: {doc_name}"):
                # Prepare audit prep prompt
                audit_prep_prompt = config.GENERAL_AI_PROMPTS["Audit Prep"].replace("[Document Type]", doc_type)

                # Query knowledge base
                st.info(f"Querying knowledge base for {doc_type} checklist...")
                if 'vector_store' not in st.session_state:
                    st.error("Knowledge base not created. Please create it in the Knowledge Base tab.")
                    return
                
                knowledge_base_context_docs = knowledge_base.query_knowledge_base(audit_prep_prompt, st.session_state.vector_store)
                knowledge_base_context = "\n".join([d.page_content for d in knowledge_base_context_docs])
                utils.show_logs(audit_prep_prompt, knowledge_base_context)
                st.success("Knowledge base query complete.")

                # Prepare final prompt
                user_query = config.GENERAL_AI_PROMPTS["Audit Query"]
                final_prompt = f"""
                **User Data:**
                {document_content}

                **Context:**
                {knowledge_base_context}

                **User Query:**
                {user_query}
                """

                # Call LLM to get audit gaps
                st.info("Analyzing document for audit gaps...")
                gaps_response = ai_integration.llm_client_instance.generate_text(final_prompt)
                utils.show_logs(final_prompt, gaps_response)
                st.success("Document analysis complete.")

                # Parse and store gaps
                gaps = [gap.strip() for gap in gaps_response.split('\n') if gap.strip()]
                for gap in gaps:
                    database.add_audit_gap(project_id, audit_reference, doc_name, gap, "New", audit_timestamp, document_content, user_query, knowledge_base_context)

        st.success("AI Audit complete!")

    # Display audit gaps
    st.subheader("Audit Gaps")
    
    audit_references = database.get_audit_references(project_id)
    audit_references = [ref for ref in audit_references if ref is not None]
    if audit_references:
        # Sort audit references by timestamp descending
        audit_references.sort(key=lambda x: datetime.strptime(x.split('-')[-1], '%Y%m%d%H%M%S'), reverse=True)
        selected_audit_reference = st.selectbox("Select Audit Reference", audit_references)

        if selected_audit_reference:
            audit_gaps_data = database.get_audit_gaps_by_reference(project_id, selected_audit_reference)
            if audit_gaps_data:
                audit_timestamp = audit_gaps_data[0][6] # Get timestamp from the first record
                st.write(f"**Audit Run Date/Time:** {audit_timestamp}")

                df = pd.DataFrame(audit_gaps_data, columns=['ID', 'Project ID', 'Audit Reference', 'Document Name', 'Comments', 'Status', 'Audit Timestamp', 'User Data', 'User Query', 'Context'])
                
                # Download button
                markdown_report = ""
                for index, row in df.iterrows():
                    markdown_report += f"## Audit Gap #{row['ID']}\n\n"
                    markdown_report += f"## Audit Gap #{row['ID']}\n\n"
                    markdown_report += f"**Document Name:** {row['Document Name']}\n\n"
                    markdown_report += f"**Audit Gap:** {row['Comments']}\n\n"
                    markdown_report += f"**Status:** {row['Status']}\n\n"
                    markdown_report += f"### User Data\n\n---\n{row["User Data"]}\n\n---"
                    markdown_report += f"### User Query\n\n---\n{row["User Query"]}\n\n---"
                    markdown_report += f"### Context\n\n---\n{row["Context"]}\n\n---"

                st.download_button(
                    label="Download Report",
                    data=markdown_report,
                    file_name=f"{selected_audit_reference}.md",
                    mime='text/markdown',
                )

                edited_df = st.data_editor(
                    df[['Document Name', 'Comments', 'Status']], 
                    column_config={
                        "Status": st.column_config.SelectboxColumn(
                            "Status",
                            options=["New", "In Progress", "Done"],
                            required=True,
                        )
                    },
                    use_container_width=True
                )
                # Here you would add logic to update the database with the edited data
    else:
        st.info("No audit gaps found.")

def training_tab(project_id):
    st.subheader("Training")

    if st.button("AI Assist", key=f"training_ai_assist_{project_id}"):
        st.session_state.ai_training_assist = True

    if st.session_state.get('ai_training_assist'):
        with st.expander("AI Training Assist", expanded=True):
            prompt = st.text_area("Prompt", value=config.GENERAL_AI_PROMPTS['Training'], key=f"training_prompt_{project_id}")
            if st.button("Generate Questions", key=f"generate_training_questions_{project_id}"):
                with st.spinner("Generating training questions..."):
                    if 'vector_store' not in st.session_state:
                        st.error("Knowledge base not created. Please create it in the Knowledge Base tab.")
                        return
                    
                    knowledge_base_context_docs = knowledge_base.query_knowledge_base(prompt, st.session_state.vector_store)
                    knowledge_base_context = "\n".join([d.page_content for d in knowledge_base_context_docs])
                    
                    response = ai_integration.llm_client_instance.generate_text(f"Context:\n{knowledge_base_context}\n\nTask: {prompt}")
                    questions = response.strip().split('\n')
                    for q in questions:
                        if '(True)' in q:
                            question = q.replace('(True)', '').strip()
                            answer = 'True'
                        elif '(False)' in q:
                            question = q.replace('(False)', '').strip()
                            answer = 'False'
                        else:
                            continue
                        database.add_training_question(project_id, question, answer, st.session_state['user_info'][0])
                    st.session_state.ai_training_assist = False
                    st.rerun()

    questions_to_answer = database.get_training_questions(project_id, st.session_state['user_info'][0])
    if questions_to_answer:
        with st.form(key=f"training_form_{project_id}"):
            answers = {}
            for q in questions_to_answer:
                answers[q[0]] = st.radio(q[2], ["True", "False"], key=f"q_{q[0]}")
            
            submitted = st.form_submit_button("Submit Answers")
            if submitted:
                for q_id, answer in answers.items():
                    database.update_training_answer(q_id, answer)
                st.rerun()

    st.subheader("Training History")
    training_history = database.get_training_history(project_id, st.session_state['user_info'][0])
    if training_history:
        df = pd.DataFrame(training_history, columns=['ID', 'Project ID', 'Question', 'User Answer', 'Actual Answer', 'User ID', 'Timestamp'])
        df['Correct'] = df['User Answer'] == df['Actual Answer']
        st.dataframe(df[['Timestamp', 'Question', 'User Answer', 'Actual Answer', 'Correct']])
        
        total_correct = df['Correct'].sum()
        total_questions = len(df)
        st.metric("Overall Score", f"{total_correct}/{total_questions} ({total_correct/total_questions:.2%})")
    else:
        st.info("No training history yet.")


def reviews_tab(project_id):
    st.subheader("Reviews")
    review_choice = st.radio("", ["My Reviews", "All Reviews"])

    if review_choice == "My Reviews":
        reviews = database.get_reviews_for_user(st.session_state['user_info'][0])
        if not reviews:
            st.info("You have no documents to review.")
        else:
            with st.expander("Review Items", expanded=True):
                review_options = {f"{r[1]} (v{database.get_document_details(r[0])[5]}) - Status: {r[3]}" : r[0] for r in reviews}
                selected_review_label = st.selectbox("Select a review to comment on:", list(review_options.keys()))

                if selected_review_label:
                    doc_id = review_options[selected_review_label]
                    doc_details = database.get_document_details(doc_id)
                    if doc_details:
                        if doc_details[3] == "Code Review": # Check if it's a code review
                            content_data = json.loads(doc_details[4])
                            payload = content_data.get("content", {})
                            if isinstance(payload, str):
                                try: payload = json.loads(payload)
                                except: payload = {}
                            
                            raw_diff = payload.get("raw_diff")
                            if raw_diff:
                                diff_html = utils.colorize_diff_to_html(raw_diff)
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
                        database.add_review(doc_id, st.session_state['user_info'][0], comment, new_status)
                        st.success("Your review has been submitted.")
                        st.rerun()

    elif review_choice == "All Reviews":
        all_reviews = database.get_all_reviews_for_project(project_id)
        if not all_reviews:
            st.info("No reviews for this project yet.")
        else:
            df = pd.DataFrame(all_reviews, columns=['Review Item', 'Version', 'Date & Timestamp', 'Document Status', 'Reviewer', 'Comments'])
            st.dataframe(df)

def admin_page():
    st.header("Admin Dashboard")
    st.subheader("Manage Users")
    users = database.get_all_users()
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

    ### Configuration

    *   **Knowledge Base**: Upload your own documents and/or scrape websites to create or update a local knowledge base for the RAG system. You can also reset the knowledge base.

    ### Admin

    *   **User Management**: Admins can view and manage all users in the system.
    """)
