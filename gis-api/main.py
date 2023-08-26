from flask import Flask, request
import pandas as pd
from fcmeans import FCM
from flask_cors import CORS

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

@app.route("/")

def hello_world():
    return "<p>Hello, World!</p>"

@app.route("/fcm", methods=['POST']) #define route for fuzzy c-means
def fcm():

    #step 1: get data from request is JSON format
    data = request.get_json()
    
    #step 2: create dataframe from JSON data
    df = pd.DataFrame(data)
    dfLengkap = df[['nama']]
    # print(df)

    #step 3: data processing
    df.drop(['kab_id', 'nama','jumlah_penduduk','latitude','longitude', 'file_geojson'], axis=1, inplace=True)

    number_clusters = 3
    fcm = FCM(n_clusters=number_clusters)
    fcm.fit(df.values)

    # step 4: get cluster result
    fcm_centers = fcm.centers
    fcm_labels = fcm.predict(df.values)
    dfLengkap['Fuzzy_cluster'] = fcm_labels
    
    #step 5: convert dataframe to JSON
    json_result = dfLengkap.to_json(orient="records")
    return json_result