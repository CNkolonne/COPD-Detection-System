from flask import Flask, request, jsonify
import tensorflow as tf
import numpy as np
import librosa
import librosa.display
from PIL import Image
import os
import soundfile as sf

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

app = Flask(__name__)

model = tf.keras.models.load_model("model.h5")

OUTPUT_DIR = r"C:\xampp\htdocs\HighflyersFinal\generated"
os.makedirs(OUTPUT_DIR, exist_ok=True)


@app.route("/predict", methods=["POST"])
def predict():

    file = request.files["file"]
    sample_id = request.form.get("sample_id", "sample")

    # Save uploaded audio
    audio_path = os.path.join(OUTPUT_DIR, f"{sample_id}.webm")
    file.save(audio_path)

    # Load audio
    y, sr = librosa.load(audio_path, sr=None)

    # Save preprocess audio
    preprocess_path = os.path.join(OUTPUT_DIR, f"{sample_id}_clean.wav")
    sf.write(preprocess_path, y, sr)

    # Create spectrogram
    spec_path = os.path.join(OUTPUT_DIR, f"{sample_id}_spec.png")

    plt.figure(figsize=(3, 3))
    S = librosa.feature.melspectrogram(y=y, sr=sr)
    librosa.display.specshow(S, sr=sr)
    plt.axis('off')
    plt.savefig(spec_path, bbox_inches='tight', pad_inches=0)
    plt.close()

    # Model input
    img = Image.open(spec_path).convert("L")
    img = img.resize((216, 128))

    img_array = np.array(img).reshape(1, 128, 216, 1) / 255.0

    prediction = model.predict(img_array)

    return jsonify({
        "prediction": prediction.tolist(),
        "preprocess_url": preprocess_path,
        "spectrogram_url": spec_path
    })


if __name__ == "__main__":
    app.run(debug=True)