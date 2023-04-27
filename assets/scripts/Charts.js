import Chart from "chart.js/auto";
import axios from "axios";
export function Charts() {
  // On fait un requête post avec Axios
  const legendContainer = document.getElementById("legend-container");
  if (!legendContainer) {
    return;
  }
  const hideIdPub = document.getElementById("hideIdPub");
  const data = new FormData();
  data.append("idPub", hideIdPub.value);
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
      const popularity = JSON.parse(response.data.popularity);
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
      const respPop = Object.keys(popularity);
      var datasPop = [];
      respPop.forEach((key) => {
        datasPop.push({
          week: key,
          count: popularity[key],
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
      //
      // Utilisation de la fonction pour obtenir le numéro de la semaine actuelle
      const today2 = new Date();
      const currentWeek2 = getWeekNumber(today);

      // Création d'un tableau de noms de semaine
      const labels2 = [];
      for (let i = 1; i <= currentWeek2; i++) {
        labels2.push("Semaine " + i);
      }
      //
      const getOrCreateLegendList = (chart, id) => {
        const legendContainer = document.getElementById(id);
        let listContainer = legendContainer.querySelector("ul");

        if (!listContainer) {
          listContainer = document.createElement("ul");
          listContainer.style.display = "flex";
          listContainer.style.flexDirection = "row";
          listContainer.style.margin = 0;
          listContainer.style.padding = 0;

          legendContainer.appendChild(listContainer);
        }

        return listContainer;
      };

      const htmlLegendPlugin = {
        id: "htmlLegend",
        afterUpdate(chart, args, options) {
          const ul = getOrCreateLegendList(chart, options.containerID);

          // Remove old legend items
          while (ul.firstChild) {
            ul.firstChild.remove();
          }

          // Reuse the built-in legendItems generator
          const items =
            chart.options.plugins.legend.labels.generateLabels(chart);

          items.forEach((item) => {
            const li = document.createElement("li");
            li.style.alignItems = "center";
            li.style.cursor = "pointer";
            li.style.display = "flex";
            li.style.flexDirection = "row";
            li.style.marginLeft = "10px";

            li.onclick = () => {
              const { type } = chart.config;
              if (type === "pie" || type === "doughnut") {
                // Pie and doughnut charts only have a single dataset and visibility is per item
                chart.toggleDataVisibility(item.index);
              } else {
                chart.setDatasetVisibility(
                  item.datasetIndex,
                  !chart.isDatasetVisible(item.datasetIndex)
                );
              }
              chart.update();
            };

            // Color box
            const boxSpan = document.createElement("span");

            boxSpan.style.background = item.fillStyle;
            boxSpan.style.borderColor = item.strokeStyle;
            boxSpan.style.borderWidth = item.lineWidth + "px";
            boxSpan.style.display = "inline-block";
            boxSpan.style.height = "20px";
            boxSpan.style.marginRight = "10px";
            boxSpan.style.width = "20px";
            boxSpan.classList.add("rounded-full", "h-5", "w-5", "mr-2", "mt-1");

            // Text
            const textContainer = document.createElement("p");

            textContainer.style.margin = 0;
            textContainer.style.padding = 0;
            textContainer.style.textDecoration = item.hidden
              ? "line-through"
              : "";

            textContainer.innerHTML = item.text;

            li.appendChild(boxSpan);
            li.appendChild(textContainer);
            ul.appendChild(li);
          });
        },
      };
      // * Chart
      new Chart(document.getElementById("chartStat"), {
        type: "bar",
        plugins: [htmlLegendPlugin],
        data: {
          labels: labels,
          datasets: [
            {
              label: "Lecteurs",
              data: datasViews.map((row) => row.count),
              borderColor: "rgb(255, 99, 132)",
              backgroundColor: "rgba(255, 99, 132, 0.2)",
              borderWidth: 1,
              pointStyle: "circle",
              cubicInterpolationMode: "monotone",
              // Nouvelle propriété pour définir la couleur de fond de la forme de point
            },
            {
              label: "J'aime",
              data: datasLikes.map((row) => row.count),
              borderColor: "rgb(255, 159, 64)",
              backgroundColor: "rgba(255, 159, 64, 0.2)",
              borderWidth: 1,
              pointStyle: "circle",
              cubicInterpolationMode: "monotone",
              // Nouvelle propriété pour définir la couleur de fond de la forme de point
            },
            {
              label: "Marquages",
              data: datasBookmark.map((row) => row.count),
              borderColor: "rgb(153, 102, 255)",
              backgroundColor: "rgba(153, 102, 255, 0.2)",
              borderWidth: 1,
              pointStyle: "circle",
              cubicInterpolationMode: "monotone",
              // Nouvelle propriété pour définir la couleur de fond de la forme de point
            },
            {
              label: "Commentaires",
              data: datasComments.map((row) => row.count),
              borderColor: "rgb(75, 192, 192)",
              backgroundColor: "rgba(75, 192, 192, 0.2)",
              borderWidth: 1,
              pointStyle: "circle",
              cubicInterpolationMode: "monotone",
              // Nouvelle propriété pour définir la couleur de fond de la forme de point
            },
          ],
        },
        options: {
          indexAxis: "y",
          plugins: {
            htmlLegend: {
              // ID of the container to put the legend in
              containerID: "legend-container",
            },
            legend: {
              display: false,
            },
          },
          scales: {
            x: {
              grid: {
                display: false,
              },
              position: "right",
              ticks: {
                beginAtZero: true,
              },
            },
            y: {
              grid: {
                display: false,
              },
            },
          },
          elements: {
            point: {
              pointStyle: "circle",
            },
          },
          animations: {
            radius: {},
          },
        },
      });

      //
      // * Chart
      new Chart(document.getElementById("chartStat2"), {
        type: "line",
        data: {
          labels: Object.keys(popularity),
          datasets: [
            {
              label: "Popularité",
              data: datasPop.map((row) => row.count),
              borderColor: "rgb(255, 99, 132)",
              backgroundColor: "rgba(255, 99, 132, 0.2)",
              borderWidth: 1,
              pointStyle: "circle",
              cubicInterpolationMode: "monotone",
              // Nouvelle propriété pour définir la couleur de fond de la forme de point
            },
          ],
        },
        options: {
          indexAxis: "y",
          plugins: {
            legend: {
              display: false,
            },
          },
          scales: {
            x: {
              grid: {
                display: false,
              },
              position: "right",
              ticks: {
                beginAtZero: true,
              },
            },
            y: {
              grid: {
                display: false,
              },
            },
          },
          elements: {
            point: {
              pointStyle: "circle",
            },
          },
          animations: {
            radius: {},
          },
        },
      });

      //
    });
  //
}
// Fonction pour obtenir le numéro de la semaine à partir d'une date donnée
function getWeekNumber(date) {
  const oneJan = new Date(date.getFullYear(), 0, 1);
  const millisecsInDay = 86400000; // Nombre de millisecondes en un jour
  return Math.ceil(
    ((date - oneJan) / millisecsInDay + oneJan.getDay() + 1) / 7
  );
}

Charts();
