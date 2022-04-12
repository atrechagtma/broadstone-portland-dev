<template>
  <div>
    <div id="mapContainer" :style="`height: 500px;`"></div>
  </div>
</template>

<script>
import "mapbox-gl/dist/mapbox-gl.css";
import Mapbox from "mapbox-gl";

export default {
  data() {
    return {
      mapStyle: "mapbox://styles/mapbox/streets-v11",
      map: {}
    };
  },
  props: {
    property: Object,
    properties: Array,
    options: Object
  },
  mounted() {
    // Mapbox
    if (
      this.options.rentpress_mapbox_maps_api_section_api_key &&
      this.property.property_latitude &&
      this.property.property_longitude
    ) {
      const longitude = parseFloat(this.property.property_longitude);
      const latitude = parseFloat(this.property.property_latitude);
      Mapbox.accessToken = this.options.rentpress_mapbox_maps_api_section_api_key;

      this.map = new Mapbox.Map({
        container: "mapContainer",
        style: "mapbox://styles/mapbox/streets-v11",
        center: [longitude, latitude],
        zoom: 15
      });

      this.map.on("load", () => {
        // if nearby properties are wanted to be visible uncomment this code
        if (this.properties) {
          this.properties.forEach(prop => {
            if (prop.property_latitude && prop.property_longitude) {
              let popup = new Mapbox.Popup({
                offset: 25,
                closeButton: false
              }).setHTML(`${prop.property_name}`);

              new Mapbox.Marker({
                color: this.$vuetify.theme.themes.light.secondary
              })
                .setLngLat([prop.property_longitude, prop.property_latitude])
                .setPopup(popup) // sets a popup on this marker
                .addTo(this.map);
            }
          });
        }

        const popup = new Mapbox.Popup({
          offset: 25,
          closeButton: false
        }).setHTML(`${this.property.property_name}`);

        const marker = new Mapbox.Marker({
          color: this.$vuetify.theme.themes.light.primary
        })
          .setLngLat([longitude, latitude])
          .setPopup(popup) // sets a popup on this marker
          .addTo(this.map);

        marker.togglePopup();
      });
    }
  },
  created() {
    if (this.options.rentpress_mapbox_maps_api_section_api_key) {
      this.mapbox = Mapbox;
    }
  }
};
</script>

<style></style>
