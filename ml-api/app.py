from flask import Flask, request, jsonify
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import PyPDF2
import re

app = Flask(__name__)

def extract_text(file):
    text = ""
    reader = PyPDF2.PdfReader(file)
    for page in reader.pages:
        text += page.extract_text() or ""
    return text

def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^a-zA-Z ]', ' ', text)
    return text

@app.route('/rank', methods=['POST'])
def rank():
    if 'resume' not in request.files:
        return jsonify({"error": "No resume uploaded"}), 400

    file = request.files['resume']
    job_text = request.form.get('job', '')

    resume_text = extract_text(file)

    resume_text = clean_text(resume_text)
    job_text = clean_text(job_text)

    vectorizer = TfidfVectorizer(stop_words='english')
    tfidf = vectorizer.fit_transform([resume_text, job_text])

    score = cosine_similarity(tfidf[0:1], tfidf[1:2])[0][0] * 100
    score = min(score * 3, 100)

    return jsonify({"score": round(score, 2)})

if __name__ == "__main__":
    app.run(port=5000)