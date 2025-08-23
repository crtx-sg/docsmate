# ai_integration.py
# This file will contain the logic for interacting with AI models.

import config
import time
import re

# Import necessary libraries for different LLM providers
try:
    from ollama import Client as OllamaClient
except ImportError:
    OllamaClient = None
    print("Warning: Ollama client not installed. Please install with 'pip install ollama' if you plan to use Ollama.")

try:
    from huggingface_hub import InferenceClient as HFInferenceClient
except ImportError:
    HFInferenceClient = None
    print("Warning: Hugging Face Hub client not installed. Please install with 'pip install huggingface_hub' if you plan to use Hugging Face.")

try:
    from openai import OpenAI
except ImportError:
    OpenAI = None
    print("Warning: OpenAI client not installed. Please install with 'pip install openai' if you plan to use OpenAI.")

try:
    from anthropic import Anthropic
except ImportError:
    Anthropic = None
    print("Warning: Anthropic client not installed. Please install with 'pip install anthropic' if you plan to use Anthropic.")

try:
    import google.generativeai as genai
except ImportError:
    genai = None
    print("Warning: Google Generative AI client not installed. Please install with 'pip install google-generativeai' if you plan to use Gemini.")

# Grok client is not a standard library, so we'll assume a placeholder or direct API call if needed.
# For now, it will be a placeholder.

class LLMClient:
    def __init__(self):
        self.provider = config.LLM_PROVIDER
        self.client = self._initialize_client()

    def _initialize_client(self):
        if self.provider == 'ollama':
            if OllamaClient:
                return OllamaClient(host=config.OLLAMA_API_BASE_URL)
            else:
                raise ImportError("Ollama client not available. Please install 'ollama'.")
        elif self.provider == 'huggingface':
            if HFInferenceClient:
                return HFInferenceClient(model=config.HUGGINGFACE_LLM_MODEL, token=config.HUGGINGFACE_API_KEY)
            else:
                raise ImportError("Hugging Face Hub client not available. Please install 'huggingface_hub'.")
        elif self.provider == 'openai':
            if OpenAI:
                return OpenAI(api_key=config.OPENAI_API_KEY)
            else:
                raise ImportError("OpenAI client not available. Please install 'openai'.")
        elif self.provider == 'anthropic':
            if Anthropic:
                return Anthropic(api_key=config.ANTHROPIC_API_KEY)
            else:
                raise ImportError("Anthropic client not available. Please install 'anthropic'.")
        elif self.provider == 'gemini':
            if genai:
                genai.configure(api_key=config.GEMINI_API_KEY)
                return genai
            else:
                raise ImportError("Google Generative AI client not available. Please install 'google-generativeai'.")
        elif self.provider == 'grok':
            # Placeholder for Grok. In a real scenario, you'd integrate with their API.
            print("Grok integration is a placeholder. You'll need to implement its API calls.")
            return None
        else:
            raise ValueError(f"Unsupported LLM provider: {self.provider}")

    def generate_text(self, prompt, model_name=None):
        if not self.client:
            return "Error: LLM client not initialized or supported."

        if self.provider == 'ollama':
            model = model_name if model_name else config.OLLAMA_LLM_MODEL
            response = self.client.chat(model=model, messages=[{'role': 'user', 'content': prompt}])
            return response['message']['content']
        elif self.provider == 'huggingface':
            # Hugging Face InferenceClient's __call__ method is used for text generation
            # The model is already configured in the client init
            response = self.client(prompt)
            return response # Assuming direct text response
        elif self.provider == 'openai':
            model = model_name if model_name else config.OPENAI_LLM_MODEL
            chat_completion = self.client.chat.completions.create(
                messages=[
                    {"role": "user", "content": prompt},
                ],
                model=model,
            )
            return chat_completion.choices[0].message.content
        elif self.provider == 'anthropic':
            model = model_name if model_name else config.ANTHROPIC_LLM_MODEL
            message = self.client.messages.create(
                model=model,
                max_tokens=1024,
                messages=[
                    {"role": "user", "content": prompt}
                ]
            )
            return message.content[0].text
        elif self.provider == 'gemini':
            model = model_name if model_name else config.GEMINI_LLM_MODEL
            model_instance = self.client.GenerativeModel(model)
            response = model_instance.generate_content(prompt)
            return response.text
        elif self.provider == 'grok':
            # Placeholder for Grok API call
            return f"Grok response for: {prompt}"
        else:
            return "Error: Unsupported LLM provider for text generation."

# Global instance of LLMClient
llm_client_instance = LLMClient()

def generate_text(prompt, model_name=None):
    return llm_client_instance.generate_text(prompt, model_name)

def generate_review(prompt, model_name=None):
    # This can now directly use the general generate_text
    return llm_client_instance.generate_text(prompt, model_name)

def generate_risk_analysis(prompt, model_name=None):
    response_text = llm_client_instance.generate_text(prompt, model_name)
    risks = [line.strip() for line in response_text.strip().split('\n') if line.strip()]
    return risks