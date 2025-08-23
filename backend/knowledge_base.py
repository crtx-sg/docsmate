# knowledge_base.py

import os
import requests
from bs4 import BeautifulSoup
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import FAISS
from langchain_community.embeddings import OllamaEmbeddings
from langchain_community.document_loaders import PyPDFLoader, TextLoader
import config
import ai_integration

def scrape_websites(urls):
    """Scrapes text content from a list of URLs."""
    all_text = ""
    for url in urls:
        try:
            response = requests.get(url)
            response.raise_for_status()  # Raise an exception for bad status codes
            soup = BeautifulSoup(response.content, 'lxml')
            # Remove script and style elements
            for script in soup(["script", "style"]):
                script.extract()
            text = soup.get_text()
            lines = (line.strip() for line in text.splitlines())
            chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
            text = '\n'.join(chunk for chunk in chunks if chunk)
            all_text += text + "\n\n"
        except requests.exceptions.RequestException as e:
            print(f"Error scraping {url}: {e}")
    return all_text

def process_uploaded_files(upload_dir):
    """Processes uploaded PDF and Markdown files."""
    all_text = ""
    for filename in os.listdir(upload_dir):
        file_path = os.path.join(upload_dir, filename)
        if filename.endswith(".pdf"):
            try:
                loader = PyPDFLoader(file_path)
                pages = loader.load_and_split()
                for page in pages:
                    all_text += page.page_content + "\n\n"
            except Exception as e:
                print(f"Error processing {filename}: {e}")
        elif filename.endswith((".md", ".txt")):
            try:
                loader = TextLoader(file_path)
                all_text += loader.load()[0].page_content + "\n\n"
            except Exception as e:
                print(f"Error processing {filename}: {e}")
    return all_text

def chunk_text(text):
    """Chunks text into smaller documents."""
    text_splitter = RecursiveCharacterTextSplitter(
        chunk_size=1000,
        chunk_overlap=200,
        length_function=len
    )
    chunks = text_splitter.split_text(text)
    return chunks

def get_embeddings_model():
    """Initializes the embeddings model from the config."""
    if config.RAG_LLM_PROVIDER == 'ollama':
        return OllamaEmbeddings(model=config.OLLAMA_EMBEDDING_MODEL, base_url=config.OLLAMA_API_BASE_URL)
    # Add other providers here as needed
    else:
        raise ValueError(f"Unsupported RAG LLM provider: {config.RAG_LLM_PROVIDER}")



def create_and_store_embeddings(chunks):
    """Creates and stores embeddings in Faiss, or loads if exists."""
    embeddings = get_embeddings_model()
    faiss_index_path = config.FAISS_INDEX_PATH
    faiss_index_file = os.path.join(faiss_index_path, "index.faiss")
    faiss_pkl_file = os.path.join(faiss_index_path, "index.pkl")

    if os.path.exists(faiss_index_file) and os.path.exists(faiss_pkl_file):
        try:
            vector_store = FAISS.load_local(faiss_index_path, embeddings, allow_dangerous_deserialization=True)
            print(f"Loaded existing Faiss index from: {faiss_index_path}")
            if chunks:
                vector_store.add_texts(texts=chunks)
                vector_store.save_local(faiss_index_path)
                print(f"Added {len(chunks)} new chunks to Faiss index: {faiss_index_path}")
        except Exception as e:
            print(f"Error loading or updating Faiss index from {faiss_index_path}: {e}. Recreating index.")
            vector_store = FAISS.from_texts(texts=chunks, embedding=embeddings)
            vector_store.save_local(faiss_index_path)
            print(f"Recreated and stored embeddings in new Faiss index: {faiss_index_path}")
    else:
        print(f"Faiss index files not found in {faiss_index_path}. Creating new index.")
        vector_store = FAISS.from_texts(texts=chunks, embedding=embeddings)
        vector_store.save_local(faiss_index_path)
        print(f"Created and stored embeddings in new Faiss index: {faiss_index_path}")
    return vector_store



import shutil

def reset_knowledge_base():
    """Deletes the Faiss knowledge base."""
    if os.path.exists(config.FAISS_INDEX_PATH):
        shutil.rmtree(config.FAISS_INDEX_PATH)
        print(f"Faiss index '{config.FAISS_INDEX_PATH}' reset successfully.")
    else:
        print(f"Faiss index '{config.FAISS_INDEX_PATH}' does not exist. Nothing to reset.")


def query_knowledge_base(query, vector_store):
    """Queries the knowledge base and returns relevant documents."""
    docs = vector_store.similarity_search(query)
    return docs

def get_answer_from_llm(query, context_docs):
    """Gets an answer from the LLM based on the retrieved documents."""
    
    context = "\n".join([doc.page_content for doc in context_docs])
    
    prompt = f"""
    **Context:**
    {context}

    **Question:**
    {query}

    **Answer:**
    """
    
    try:
        response = ai_integration.llm_client_instance.generate_text(prompt)
    except Exception as e:
        print(f"Error generating text from LLM: {e}")
        raise
    
    return response