import L from "leaflet";
import "leaflet/dist/leaflet.css";

let activeLayer = null;
const loaderLayers = {};
const loadedData = {};

const bounds = [
  [0, 0],
  [-255, 255],
];
const map = L.map("map", {
  center: [-128, 128],
  zoom: 4,
  minZoom: 1,
  crs: L.CRS.Simple,
}).setView([128, -128], 1);

map.fitBounds(bounds);
map.setMaxBounds(bounds);

const layerOptions = {
  maxNativeZoom: 5,
  maxZoom: 7,
  minZoom: 1,
  crs: L.CRS.Simple,
};
const layerControl = L.control.layers().addTo(map);

let lastLayer = null;

Object.keys(mapInfo.levels).forEach((level) => {
  const layer = L.tileLayer(
    `https://renderbus.statbus.space/tiles/${mapInfo.dmmPath.filename}-${
      level - 1
    }/{z}/tile_{x}-{y}.png`,
    {
      ...layerOptions,
      metadata: { zLevel: Number(level) },
    }
  );

  layerControl.addBaseLayer(layer, `${mapInfo.name} - ${level}`);
  lastLayer = layer;
});

if (lastLayer) {
  map.addLayer(lastLayer);
  activeLayer = lastLayer;
}

const loaders = document.querySelectorAll(".data");
loaders.forEach((loader) => {
  loader.addEventListener("click", async (e) => {
    e.preventDefault();
    loader.classList.toggle("text-muted");
    await updatePolygons();
  });
});

map.on("baselayerchange", async (e) => {
  activeLayer = e.layer;
  await updatePolygons();
});

async function updatePolygons() {
  const currentLayer = activeLayer || getActiveBaseLayer();
  if (!currentLayer) return;

  const currentZ = currentLayer.options.metadata.zLevel;
  const activeLoaders = Array.from(
    document.querySelectorAll(".data:not(.text-muted)")
  ).map((al) => al.dataset.key);

  Object.keys(loaderLayers).forEach((key) => {
    // if (!activeLoaders.includes(key)) {
    loaderLayers[key].clearLayers();
    // }
  });

  for (const key of activeLoaders) {
    if (!loaderLayers[key]) {
      loaderLayers[key] = L.layerGroup().addTo(map);
    }

    const data = await fetchData(key);
    if (!data) continue;
    const dataArray = Object.values(data);
    dataArray
      .filter((e) => e.z === currentZ)
      .forEach((e) => {
        const lat = e.y - 255 - 0.5;
        const lng = e.x - 0.5 - 1;
        if (key === "explosion") {
          const maxRadius = Math.max(e.dev, e.heavy, e.light, e.flash, e.flame);
          if (e.flame > 0) {
            const flame = L.circle([lat, lng], {
              radius: e.flame + 0.5,
              color: "orange",
            }).addTo(loaderLayers[key]);
          }
          if (e.flash > 0) {
            const flash = L.circle([lat, lng], {
              radius: e.flash + 0.5,
              color: "white",
            }).addTo(loaderLayers[key]);
          }
          if (e.light > 0) {
            const light = L.circle([lat, lng], {
              radius: e.light + 0.5,
              color: "yellow",
            }).addTo(loaderLayers[key]);
          }
          if (e.heavy > 0) {
            const heavy = L.circle([lat, lng], {
              radius: e.heavy + 0.5,
              color: "orange",
            }).addTo(loaderLayers[key]);
          }
          if (e.dev > 0) {
            const dev = L.circle([lat, lng], {
              radius: e.dev + 0.5,
              color: "red",
            }).addTo(loaderLayers[key]);
          }

          const explosion = L.circle([lat, lng], {
            radius: maxRadius + 0.5,
            color: "transparent",
            fillColor: "transparent",
          }).addTo(loaderLayers[key]);
          explosion.bindPopup(`Explosion at ${e.area} (${e.x},${e.y}) with effects:<br>
            <span class='badge text-bg-danger'>Devastation</span> ${e.dev}<br>
            <span class='badge text-bg-warning'>Heavy</span> ${e.heavy}<br>
            <span class='badge text-dark' style='background: yellow;'>Light</span> ${e.light}<br>
            <span class='badge text-bg-light'>Flash</span> ${e.flash}<br>
            <span class='badge text-bg-warning'>Flame</span> ${e.flame}<br>
            Occurred at ${e.time}`);
        } else if ("death" === key) {
          const polygon = L.polygon(
            [
              [lat + 0.5, lng + 0.5],
              [lat + 0.5, lng + 0.5 + 1],
              [lat + 0.5 - 1, lng + 0.5 + 1],
              [lat + 0.5 - 1, lng + 0.5],
            ],
            { color: "red" }
          ).bindPopup(
            `<strong>${e.name}(<a href="/player/${e.ckey}">${e.ckey}</a>)</strong> the ${e.job}<br>in ${e.location} @ ${e.datetime}`
          );
          loaderLayers[key].addLayer(polygon);
        } else {
          const polygon = L.polygon(
            [
              [lat + 0.5, lng + 0.5],
              [lat + 0.5, lng + 0.5 + 1],
              [lat + 0.5 - 1, lng + 0.5 + 1],
              [lat + 0.5 - 1, lng + 0.5],
            ],
            { color: "blue" }
          );
          loaderLayers[key].addLayer(polygon);
        }
      });
  }
}

async function fetchData(key) {
  if (loadedData[key]) return loadedData[key];

  try {
    const response = await fetch(`/api/v1/round/${roundInfo.id}/${key}`);
    if (!response.ok) throw new Error(`Response status: ${response.status}`);
    const result = await response.json();
    loadedData[key] = result;
    return result;
  } catch (err) {
    console.error(err);
    return null;
  }
}

function getActiveBaseLayer() {
  let active = null;
  map.eachLayer((layer) => {
    if (layer instanceof L.TileLayer && layer.options.metadata?.zLevel) {
      active = layer;
    }
  });
  return active;
}

function tileGridCircleOutline(centerLat, centerLng, radius) {
  const points = [];
  for (let dx = -radius; dx <= radius; dx++) {
    for (let dy = -radius; dy <= radius; dy++) {
      if (Math.abs(dx) === radius || Math.abs(dy) === radius) {
        points.push([centerLat + dy, centerLng + dx]);
      }
    }
  }

  points.sort(
    (a, b) =>
      Math.atan2(a[0] - centerLat, a[1] - centerLng) -
      Math.atan2(b[0] - centerLat, b[1] - centerLng)
  );

  return points;
}

updatePolygons();
