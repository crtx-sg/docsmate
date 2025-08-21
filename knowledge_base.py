# knowledge_base.py

import os
import requests
from bs4 import BeautifulSoup
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import Milvus
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
        elif filename.endswith(".md"):
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
        return OllamaEmbeddings(model=config.OLLAMA_EMBEDDING_MODEL)
    # Add other providers here as needed
    else:
        raise ValueError(f"Unsupported RAG LLM provider: {config.RAG_LLM_PROVIDER}")

from pymilvus import connections, Collection, utility

def create_and_store_embeddings(chunks):
    """Creates and stores embeddings in Milvus, or loads if exists."""
    embeddings = get_embeddings_model()
    connections.connect(host=config.MILVUS_HOST, port=config.MILVUS_PORT)
    
    collection_name = "docsmate_knowledge_base"
    if utility.has_collection(collection_name):
        # If collection exists, load it
        vector_store = Milvus(
            embedding_function=embeddings,
            connection_args={"host": config.MILVUS_HOST, "port": config.MILVUS_PORT},
            collection_name=collection_name,
            auto_id=True
        )
        print(f"Loaded existing Milvus collection: {collection_name}")
    else:
        # If collection does not exist, create and insert
        vector_store = Milvus.from_texts(
            texts=chunks,
            embedding=embeddings,
            connection_args={"host": config.MILVUS_HOST, "port": config.MILVUS_PORT},
            collection_name=collection_name,
            auto_id=True
        )
        print(f"Created and stored embeddings in new Milvus collection: {collection_name}")
    return vector_store

def update_embeddings(new_chunks):
    """Adds new chunks to an existing Milvus collection."""
    embeddings = get_embeddings_model()
    connections.connect(host=config.MILVUS_HOST, port=config.MILVUS_PORT)
    
    collection_name = "docsmate_knowledge_base"
    if not utility.has_collection(collection_name):
        raise ValueError("Knowledge base does not exist. Please create it first.")
        
    vector_store = Milvus(
        embedding_function=embeddings,
        connection_args={"host": config.MILVUS_HOST, "port": config.MILVUS_PORT},
        collection_name=collection_name,
        auto_id=True
    )
    vector_store.add_texts(new_chunks)
    print(f"Added {len(new_chunks)} new chunks to Milvus collection: {collection_name}")
    return vector_store

def reset_knowledge_base():
    """Deletes the Milvus knowledge base collection."""
    connections.connect(host=config.MILVUS_HOST, port=config.MILVUS_PORT)
    collection_name = "docsmate_knowledge_base"
    if utility.has_collection(collection_name):
        utility.drop_collection(collection_name)
        print(f"Milvus collection '{collection_name}' reset successfully.")
    else:
        print(f"Milvus collection '{collection_name}' does not exist. Nothing to reset.")


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
    
    response = ai_integration.llm_client_instance.generate_text(prompt)
    
    return response
