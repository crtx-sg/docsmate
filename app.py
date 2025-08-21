import streamlit as st
import os

import config
import database
import ui_components
import utils

# --- DATABASE SETUP ---

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
                user = database.login_user(email, password)
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
                database.add_user(new_user, new_password, new_email)
                st.success("You have successfully created an account")
                st.info("Go to Login Menu to login")
    else:
        st.sidebar.subheader(f"Welcome {st.session_state['user_info'][1]}")
        st.session_state.show_logs = st.sidebar.toggle("Show App Logs", value=False)
        
        nav_options = ["Dashboard", "Projects", "Templates", "Knowledge Base", "Admin", "Help"]
        if st.session_state.show_logs:
            nav_options.append("App Logs")

        page = st.sidebar.radio("Navigation", nav_options)

        if st.sidebar.button("Logout"):
            st.session_state['logged_in'] = False
            st.session_state['user_info'] = None
            st.rerun()

        st.sidebar.markdown('''---
        v1.0, Coherentix Labs
        ''')

        if page == "Dashboard":
            ui_components.dashboard_page()
        elif page == "Projects":
            ui_components.projects_page()
        elif page == "Templates":
            ui_components.templates_page()
        elif page == "Knowledge Base":
            ui_components.knowledge_base_page()
        elif page == "App Logs":
            ui_components.app_logs_page()
        elif page == "Admin" and st.session_state['user_info'][4]:
            ui_components.admin_page()
        elif page == "Help":
            ui_components.help_page()
        elif page == "Admin" and not st.session_state['user_info'][4]:
            st.warning("You do not have admin privileges.")


if __name__ == '__main__':
    database.init_db()
    if not os.path.exists(config.UPLOAD_DIRECTORY):
        os.makedirs(config.UPLOAD_DIRECTORY)
    try:
        migrated = database.migrate_code_review_content()
        if migrated:
            print(f"Migrated {migrated} legacy code review item(s).")
    except Exception as e:
        print(f"Migration skipped due to error: {e}")
    main()
