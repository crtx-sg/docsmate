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

# --- RISK STATUS OPTIONS ---
RISK_STATUS_OPTIONS = ["New", "Ignore", "Minor", "Major", "Severe"]

# --- ASIL (Automotive Safety Integrity Level) CONFIGURATION ---
ASIL_SEVERITY = {
    "S0: No injuries": 0,
    "S1: Light to moderate injuries": 1,
    "S2: Severe injuries": 2,
    "S3: Life-threatening injuries (survival uncertain), fatal injuries": 3
}

ASIL_EXPOSURE = {
    "E0: Incredible (probability of exposure is negligible)": 0,
    "E1: Very low probability (less than 1% of average operating time)": 1,
    "E2: Low probability (1% to 10% of average operating time)": 2,
    "E3: Medium probability (10% to 100% of average operating time)": 3,
    "E4: High probability (more than 100% of average operating time, e.g., always present)": 4
}

ASIL_CONTROLLABILITY = {
    "C0: Controllable in general (100% of drivers or other traffic participants can usually avoid harm)": 0,
    "C1: Simply controllable (99% of drivers or other traffic participants can usually avoid harm)": 1,
    "C2: Normally controllable (90% or more of all drivers or other traffic participants are usually able to avoid harm)": 2,
    "C3: Difficult to control or uncontrollable (less than 90% of drivers or other traffic participants can usually avoid harm)": 3
}

ASIL_MAPPING = {
    "S1": { "E1": { "C1": "QM", "C2": "QM", "C3": "QM" }, "E2": { "C1": "QM", "C2": "QM", "C3": "QM" }, "E3": { "C1": "QM", "C2": "QM", "C3": "A" }, "E4": { "C1": "QM", "C2": "A", "C3": "B" } },
    "S2": { "E1": { "C1": "QM", "C2": "QM", "C3": "QM" }, "E2": { "C1": "QM", "C2": "QM", "C3": "A" }, "E3": { "C1": "QM", "C2": "A", "C3": "B" }, "E4": { "C1": "A", "C2": "B", "C3": "C" } },
    "S3": { "E1": { "C1": "QM", "C2": "QM", "C3": "A" }, "E2": { "C1": "QM", "C2": "A", "C3": "B" }, "E3": { "C1": "A", "C2": "B", "C3": "C" }, "E4": { "C1": "B", "C2": "C", "C3": "D" } }
}

ASIL_RATING_TABLE = {
    (3+4+3): "D", (3+4+2): "C", (3+4+1): "B",
    (3+3+3): "C", (3+3+2): "B", (3+3+1): "A",
    (3+2+3): "B", (3+2+2): "A", (3+2+1): "QM",
    (3+1+3): "A", (3+1+2): "QM", (3+1+1): "QM",

    (2+4+3): "C", (2+4+2): "B", (2+4+1): "A",
    (2+3+3): "B", (2+3+2): "A", (2+3+1): "QM",
    (2+2+3): "A", (2+2+2): "QM", (2+2+1): "QM",

    (1+4+3): "B", (1+4+2): "A", (1+4+1): "QM",
    (1+3+3): "A", (1+3+2): "QM", (1+3+1): "QM",
}

# --- SIL (Safety Integrity Level) CONFIGURATION ---
SIL_CONSEQUENCE = {
    "C1: Minor injury": 1,
    "C2: Serious permanent injury to one or more persons; death to one person": 2,
    "C3: Death to several people": 3,
    "C4: Very many people killed": 4
}

SIL_EXPOSURE = {
    "F1: Rare to more often exposure in the hazardous zone. Exposure time is short": 1,
    "F2: Frequent to permanent exposure in the hazardous zone": 2
}

SIL_AVOIDANCE = {
    "P1: Possible under certain conditions": 1,
    "P2: Almost impossible": 2
}

SIL_PROBABILITY = {
    "W1: A very slight probability that the unwanted occurrence will come to pass and only a few unwanted occurrences are likely": 1,
    "W2: A slight probability that the unwanted occurrence will come to pass and few unwanted occurrences are likely": 2,
    "W3: A relatively high probability that the unwanted occurrence will come to pass and several unwanted occurrences are likely": 3
}

SIL_MAPPING = {
    "W1": { "C1": { "F1": { "P1": "---", "P2": "---" }, "F2": { "P1": "---", "P2": "a" } }, "C2": { "F1": { "P1": "---", "P2": "a" }, "F2": { "P1": "a", "P2": "1" } }, "C3": { "F1": { "P1": "a", "P2": "1" }, "F2": { "P1": "1", "P2": "2" } }, "C4": { "F1": { "P1": "1", "P2": "2" }, "F2": { "P1": "2", "P2": "3" } } },
    "W2": { "C1": { "F1": { "P1": "---", "P2": "a" }, "F2": { "P1": "a", "P2": "1" } }, "C2": { "F1": { "P1": "a", "P2": "1" }, "F2": { "P1": "1", "P2": "2" } }, "C3": { "F1": { "P1": "1", "P2": "2" }, "F2": { "P1": "2", "P2": "3" } }, "C4": { "F1": { "P1": "2", "P2": "3" }, "F2": { "P1": "3", "P2": "4" } } },
    "W3": { "C1": { "F1": { "P1": "a", "P2": "1" }, "F2": { "P1": "1", "P2": "2" } }, "C2": { "F1": { "P1": "1", "P2": "2" }, "F2": { "P1": "2", "P2": "3" } }, "C3": { "F1": { "P1": "2", "P2": "3" }, "F2": { "P1": "3", "P2": "4" } }, "C4": { "F1": { "P1": "3", "P2": "4" }, "F2": { "P1": "4", "P2": "b" } } }
}

SIL_RATING_TABLE = {
    (1, 1, 1, 1): "---", (1, 1, 1, 2): "---", (1, 1, 2, 1): "---", (1, 1, 2, 2): "a",
    (1, 2, 1, 1): "---", (1, 2, 1, 2): "a", (1, 2, 2, 1): "a", (1, 2, 2, 2): "1",
    (1, 3, 1, 1): "a", (1, 3, 1, 2): "1", (1, 3, 2, 1): "1", (1, 3, 2, 2): "2",
    (1, 4, 1, 1): "1", (1, 4, 1, 2): "2", (1, 4, 2, 1): "2", (1, 4, 2, 2): "3",

    (2, 1, 1, 1): "---", (2, 1, 1, 2): "a", (2, 1, 2, 1): "a", (2, 1, 2, 2): "1",
    (2, 2, 1, 1): "a", (2, 2, 1, 2): "1", (2, 2, 2, 1): "1", (2, 2, 2, 2): "2",
    (2, 3, 1, 1): "1", (2, 3, 1, 2): "2", (2, 3, 2, 1): "2", (2, 3, 2, 2): "3",
    (2, 4, 1, 1): "2", (2, 4, 1, 2): "3", (2, 4, 2, 1): "3", (2, 4, 2, 2): "4",

    (3, 1, 1, 1): "a", (3, 1, 1, 2): "1", (3, 1, 2, 1): "1", (3, 1, 2, 2): "2",
    (3, 2, 1, 1): "1", (3, 2, 1, 2): "2", (3, 2, 2, 1): "2", (3, 2, 2, 2): "3",
    (3, 3, 1, 1): "2", (3, 3, 1, 2): "3", (3, 3, 2, 1): "3", (3, 3, 2, 2): "4",
    (3, 4, 1, 1): "3", (3, 4, 1, 2): "4", (3, 4, 2, 1): "4", (3, 4, 2, 2): "b",
}

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

