# Docsmate - Document Lifecycle Management System

## Overview

Docsmate is a comprehensive, AI-enhanced platform for managing the entire lifecycle of documents in regulated engineering industries such as medical, automotive, and industrial. It provides a suite of tools for project management, document creation and collaboration, risk management, and traceability, all within a secure, user-friendly environment.

## Key Features

- **Project Management**: Create and manage projects, each with its own set of documents, team members, and risk assessments.
- **AI-Powered Document Creation**: Leverage the power of AI to generate documents from scratch, create templates, and assist with content creation.
- **Rich-Text Editor**: A full-featured Quill-based editor for creating and formatting documents with ease.
- **Template Management**: Create and reuse document templates to standardize your documentation process.
- **Review and Approval Workflow**: A built-in review system with commenting and status tracking to ensure document quality and compliance.
- **Risk Management**: Identify, assess, and mitigate project risks with an integrated risk management module.
- **Traceability Matrix**: Track the relationships between requirements, design specifications, and test cases.
- **PDF Export**: Export your documents to PDF for easy sharing and archiving.
- **Application Logging**: An optional logging feature to track AI interactions and other important events.

## Tech Stack

- **Frontend**: Streamlit
- **Backend**: FastAPI
- **Database**: SQLite
- **AI Integration**: Ollama / Hugging Face (configurable)
- **Containerization**: Docker

## Installation and Setup

### Prerequisites

- Docker
- Docker Compose

### Running the Application

1.  **Clone the repository**:
    ```bash
    git clone <repository_url>
    cd docsmate
    ```

2.  **Build and run the containers**:
    ```bash
    docker-compose up --build
    ```

3.  **Access the application**:
    - The Streamlit frontend will be available at `http://localhost:8501`.
    - The FastAPI backend will be available at `http://localhost:8000`.

## Project Structure

- `app.py`: The main Streamlit application file.
- `backend.py`: The FastAPI backend application.
- `config.py`: Configuration file for AI models, prompts, and other settings.
- `ai_integration.py`: Module for handling interactions with AI models.
- `requirements.txt`: Python dependencies for the project.
- `Dockerfile`: Defines the Docker image for the application.
- `docker-compose.yml`: Defines the services for the frontend and backend.

