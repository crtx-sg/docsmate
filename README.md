# Docsmate

Docsmate is an all-in-one solution for document lifecycle management in regulated engineering industries. It provides a collaborative platform for teams to manage their project documentation, ensuring compliance and traceability throughout the development process.

## Features

*   **Project Management**: Create and manage projects, assign team members, and track project progress.
*   **Document Control**: A powerful rich-text editor for creating and managing documents, with support for templates and versioning.
*   **Code Review**: A dedicated module for conducting code reviews with a diff viewer.
*   **Workflow Management**: A flexible review and approval workflow to ensure document quality and compliance.
*   **Hazard Analysis**: A built-in tool for conducting hazard analysis according to industry standards such as IEC 62304, ISO 26262, and IEC 61508.
*   **Traceability Matrix**: Easily create and manage traceability matrices to link requirements, design, and testing artifacts.
*   **AI-Powered Assistance**: Leverage the power of AI to assist with document creation, review, and audit.
*   **Knowledge Base**: Build a project-specific knowledge base by uploading documents or scraping websites, and then chat with it to get answers to your questions.
*   **Training**: Generate training questions from your knowledge base to test your team's understanding of project documentation and SOPs.
*   **Admin Dashboard**: A simple interface for managing users and system settings.

## Installation

Docsmate is designed to be run with Docker and Docker Compose.

### Prerequisites

*   Docker
*   Docker Compose

### Running the Application

1.  **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd docsmate
    ```

2.  **Build and start the containers:**
    ```bash
    docker compose build
    docker compose up -d
    ```

3.  **Access the application:**
    *   The frontend is available at `http://localhost:8501`.
    *   The backend API is available at `http://localhost:8000`.

### Resetting Application Data

To reset all application data (including user accounts, projects, documents, and knowledge base), you need to remove the Docker volumes associated with the application.

1.  **Stop the application:**
    ```bash
    docker compose down
    ```

2.  **Remove the volumes:**
    ```bash
    docker volume rm docsmate_db_data docsmate_faiss_data
    ```

3.  **Start the application again:**
    ```bash
    docker compose up -d
    ```

## Usage

1.  **Sign up and log in:** Create a new user account and log in to the application.
2.  **Create a project:** Go to the "Projects" page and create a new project.
3.  **Add team members:** Go to the "Team" tab within your project to add team members.
4.  **Start creating documents:** Use the "Documents" tab to create, edit, and manage your project documentation.
5.  **Explore other features:** Explore the other features of the application, such as the Hazard Analysis, Traceability Matrix, and Knowledge Base.
