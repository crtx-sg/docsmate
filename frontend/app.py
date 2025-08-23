import streamlit as st
import os
import requests
import ui_components

API_URL = "http://backend:8000"

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
                response = requests.post(f"{API_URL}/login", json={"email": email, "password": password})
                if response.status_code == 200:
                    user = response.json()['user']
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
                response = requests.post(f"{API_URL}/signup", json={"username": new_user, "email": new_email, "password": new_password})
                if response.status_code == 200:
                    st.success("You have successfully created an account")
                    st.info("Go to Login Menu to login")
                else:
                    st.error("Error creating account")

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
    main()