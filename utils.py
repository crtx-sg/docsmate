import streamlit as st
from datetime import datetime
import html
import difflib
from io import BytesIO
from xhtml2pdf import pisa

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
    # Ensure html_content is a string
    if isinstance(html_content, dict):
        # Try to extract content if it's a dictionary
        html_content = html_content.get("content", "")
        if not isinstance(html_content, str):
            print("Warning: html_content is still not a string after extraction attempt.")
            return None # Or raise an error, depending on desired behavior
    
    if not isinstance(html_content, str):
        print(f"Error: Expected html_content to be a string, but got {type(html_content)}")
        return None # Cannot process non-string content

    pdf_file = BytesIO()
    pisa_status = pisa.CreatePDF(BytesIO(html_content.encode('utf-8')), dest=pdf_file)
    if pisa_status.err:
        return None
    pdf_file.seek(0)
    return pdf_file.getvalue()

def colorize_diff_to_html(diff_text):
    """Converts diff text to colorized HTML."""
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
