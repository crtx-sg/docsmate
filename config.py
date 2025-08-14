# config.py

# --- DOCUMENT TYPES ---
# Predefined list of document types for standardization.
DOCUMENT_TYPES = [
    "Design Plan",
    "User Needs",
    "Design Inputs",
    "Design Outputs",
    "Risk Analysis",
    "Human Factors Analysis",
    "Design Verification",
    "Design Validation",
    "Design Changes",
    "Software Validation",
    "Design Reviews",
    "Design Transfer"
]

# --- GENERAL LLM CONFIGURATION ---
LLM_PROVIDER = 'ollama'
HUGGINGFACE_API_KEY = "YOUR_HUGGINGFACE_API_KEY"
HUGGINGFACE_LLM_MODEL = "mistralai/Mistral-7B-Instruct-v0.1"
HUGGINGFACE_EMBEDDING_MODEL = "sentence-transformers/all-MiniLM-L6-v2"
OLLAMA_LLM_MODEL = "qwen2:7b"  # Specify the Ollama model to use
OLLAMA_EMBEDDING_MODEL = "nomic-embed-text:latest" # Specify the Ollama model for embeddings

# --- RAG CONFIGURATION ---
RAG_LLM_PROVIDER = 'ollama'
MILVUS_HOST = "localhost"
MILVUS_PORT = "19530"

# --- KNOWLEDGE BASE & SCRAPING ---
UPLOAD_DIRECTORY = "uploaded_files"
WEBSITES_TO_SCRAPE = [
    "https://www.fda.gov/medical-devices/device-advice-comprehensive-regulatory-assistance",
    "https://www.medical-device-regulation.eu/mdcg-endorsed-documents/"
]

# --- AI PROMPTS FOR DOCUMENT CREATION ---
# Prompts used when creating a new document with the "Using AI" option.
NEW_DOCUMENT_PROMPTS = {
    "Design Plan": "Generate a comprehensive Design Plan for a [configurable_item]. Include sections for introduction, scope, resources, and timeline.",
    "User Needs": "Create a detailed User Needs document for a [configurable_item]. List potential user requirements, target audience considerations, and use environments.",
    "Design Inputs": "Develop a Design Inputs document for a [configurable_item]. Translate user needs into technical specifications and performance requirements.",
    "Design Outputs": "Produce a Design Outputs document for a [configurable_item], detailing the final specifications, drawings, and material requirements.",
    "Risk Analysis": "Conduct a thorough Risk Analysis for a [configurable_item] based on ISO 14971. Identify potential hazards, assess their risks, and propose mitigation strategies.",
    "Human Factors Analysis": "Generate a Human Factors Analysis report for a [configurable_item]. Focus on user interface design, usability, and potential use errors.",
    "Design Verification": "Create a Design Verification plan for a [configurable_item]. Define test protocols, acceptance criteria, and the methods to confirm that design inputs are met.",
    "Design Validation": "Lay out a Design Validation plan for a [configurable_item]. The plan should ensure the device meets user needs and intended uses, possibly including clinical or simulated use studies.",
    "Design Changes": "Draft a Design Change protocol document for a [configurable_item]. It should outline the process for requesting, evaluating, and implementing design changes.",
    "Software Validation": "Generate a Software Validation plan for a [configurable_item] with software components. Include sections on unit, integration, and system testing.",
    "Design Reviews": "Create a template for conducting formal Design Reviews for a [configurable_item]. Include agenda items, a list of typical attendees, and expected outcomes.",
    "Design Transfer": "Outline a Design Transfer plan for a [configurable_item] to move it from development to manufacturing. Detail the steps for process validation and quality control."
}

# --- AI PROMPTS FOR DOCUMENT REVIEW ---
# Prompts used by the "AI Assist" feature in the review section.
REVIEW_PROMPTS = {
    "Design Plan": "Review the following Design Plan for a [configurable_item]. Check for completeness, clarity, and feasibility. Identify any potential gaps in the project scope, timeline, or resource allocation.",
    "User Needs": "Analyze the following User Needs document for a [configurable_item]. Are the user needs well-defined, comprehensive, and verifiable? Suggest any missing requirements.",
    "Design Inputs": "Examine the following Design Inputs for a [configurable_item]. Do they correctly translate user needs into technical specifications? Are they clear, unambiguous, and testable?",
    "Design Outputs": "Review the provided Design Outputs for a [configurable_item]. Do they meet the design input requirements? Check for consistency and completeness of the specifications.",
    "Risk Analysis": "Critically review the following Risk Analysis for a [configurable_item]. Are the hazard identifications thorough? Is the risk evaluation sound? Are the proposed mitigation strategies adequate?",
    "Human Factors Analysis": "Assess the following Human Factors Analysis for a [configurable_item]. Does it adequately address usability and potential use-related hazards? Suggest improvements.",
    "Design Verification": "Evaluate the following Design Verification plan for a [configurable_item]. Are the test methods appropriate? Is the sample size justified? Are the acceptance criteria clear?",
    "Design Validation": "Review the following Design Validation plan for a [configurable_item]. Will the planned activities be sufficient to validate that the device meets user needs?",
    "Design Changes": "Analyze the following Design Change document for a [configurable_item]. Has the impact of the change been fully assessed? Is the verification and validation plan for the change adequate?",
    "Software Validation": "Examine the following Software Validation plan for a [configurable_item]. Does it cover all software requirements and address potential failure modes and cybersecurity risks?",
    "Design Reviews": "Review the outcomes of the following Design Review for a [configurable_item]. Were all critical aspects covered? Are the action items clear and assigned?",
    "Design Transfer": "Assess the following Design Transfer plan for a [configurable_item]. Is it comprehensive enough to ensure a smooth transition to manufacturing without compromising quality?"
}

# --- GENERAL AI PROMPTS ---
# Used by the editor's AI Assistant for general queries.
GENERAL_AI_PROMPTS = {
    "Summarize": "Summarize the key points of the provided text.",
    "Explain": "Explain the concept of [configurable_item] in simple terms based on the text.",
    "Improve Writing": "Improve the clarity and conciseness of the following text.",
    "Check for Inconsistencies": "Review the following text for any inconsistencies or contradictions."
}

