import { Chart } from "chart.js";
import axios from "axios";
export function Charts() {
  // On fait un requête post avec Axios
  const hideIdPub = document.getElementById("hideIdPub");
  if (!hideIdPub) {
    return;
  }
  const data = new FormData();
  data.append("idPub", hideIdPub.value);
  console.log(hideIdPub.value);
  axios
    .post("/publication/chart", data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      const nbrView = JSON.parse(response.data.views);
      const nbrBookmark = JSON.parse(response.data.bookmarks);
      const nbrLikes = JSON.parse(response.data.likes);
      const nbrComments = JSON.parse(response.data.comments);
      // On map
      let n = 0;
      const respView = Object.keys(nbrView);
      var datasViews = [];
      respView.forEach((key) => {
        datasViews.push({
          week: key,
          count: nbrView[key],
        });
      });
      // On map
      n = 0;
      const respBookmark = Object.keys(nbrBookmark);
      var datasBookmark = [];
      respBookmark.forEach((key) => {
        datasBookmark.push({
          week: key,
          count: nbrBookmark[key],
        });
      });
      // On map
      n = 0;
      const respLikes = Object.keys(nbrLikes);
      var datasLikes = [];
      respLikes.forEach((key) => {
        datasLikes.push({
          week: key,
          count: nbrLikes[key],
        });
      });
      // On map
      n = 0;
      const respComments = Object.keys(nbrComments);
      var datasComments = [];
      respComments.forEach((key) => {
        datasComments.push({
          week: key,
          count: nbrComments[key],
        });
      });
      //
      var today = new Date();
      var currentMonth = today.getMonth();

      var labels = [];
      for (var i = 0; i <= currentMonth; i++) {
        labels.push(
          new Date(2000, i).toLocaleString("default", { month: "short" })
        );
      }
      //
      // * Chart
      new Chart(document.getElementById("ChartNbrViews"), {
        type: "line",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Vues",
              data: datasViews.map((row) => row.count),
              borderColor: "LightBlue", // Ajout de la propriété borderColor avec la valeur 'red'
              backgroundColor: "transparent", // Ajout de la propriété borderColor avec la valeur 'red'
            },
            {
              label: "J'aime",
              data: datasLikes.map((row) => row.count),
              borderColor: "LightCoral", // Ajout de la propriété borderColor avec la valeur 'red'
              backgroundColor: "transparent", // Ajout de la propriété borderColor avec la valeur 'red'
            },
            {
              label: "Marques",
              data: datasBookmark.map((row) => row.count),
              borderColor: "LightGreen",
              backgroundColor: "transparent", // Ajout de la propriété borderColor avec la valeur 'red'
            },
            {
              label: "Commentaires",
              data: datasComments.map((row) => row.count),
              borderColor: "LightGrey",
              backgroundColor: "transparent", // Ajout de la propriété borderColor avec la valeur 'red'
            },
          ],
        },
        options: {
          height: 600,
          scales: {
            yAxes: [
              {
                ticks: {
                  beginAtZero: true,
                },
                gridLines: {
                  display: false,
                },
              },
            ],
            xAxes: [
              {
                gridLines: {
                  display: false,
                },
              },
            ],
          },
        },
      });
    });
  //
}
Charts();
