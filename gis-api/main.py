from flask import Flask

app = Flask(__name__)

@app.route("/")
def hello_world():
    return "<p>Hello, World!</p>"

@app.route("/fcm") #define route for fuzzy c-means
def fcm():
    return "<p>FCM</p>"