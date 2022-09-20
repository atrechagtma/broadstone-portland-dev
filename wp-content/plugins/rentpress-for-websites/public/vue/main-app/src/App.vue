<template>
  <v-app class="rentpress-shortcode-wrapper" :style="userStyles">
    <v-main class="rentpress-inherited-font-family">
      <div v-if="shortcode == 'single-floorplan-page'">
        <single-floorplan-page
          v-if="floorplanObject && floorplanObject.floorplan_code"
          :floorplan="floorplanObject"
          :similarFloorplans="floorplansArray"
          :options="optionsObject"
          :formatter="formatter"
          :shortcode="shortcode"
        />
      </div>
      <div v-else-if="shortcode == 'single-property-page'">
        <single-property-page
          v-if="propertyObject && propertyObject.property_code"
          :property="propertyObject"
          :properties="propertiesArray"
          :options="optionsObject"
          :hasGalleryShortcode="hasGalleryShortcode"
          :hideNeighborhood="hideNeighborhood"
          :showMap="hasMapData"
          :city="cityObject"
          :neighborhood="neighborhoodObject"
          :formatter="formatter"
          :useModals="useModals"
        >
          <template v-slot:map>
            <neighborhood-map
              :property="propertyObject"
              :properties="propertiesArray"
              :options="optionsObject"
            />
          </template>
        </single-property-page>
        <error-page
          v-else
          :property="propertyObject"
          title=""
          message="Could not fetch apartment at this time. Please try again later."
        />
      </div>
      <div v-else-if="shortcode == 'floorplan-search'">
        <floorplan-search
          v-if="floorplansArray[0] && floorplansArray[0].floorplan_code"
          :floorplans="floorplansArray"
          :parentproperties="parentPropertiesArray"
          :hidecommunityfilter="hideCommunityFilterOption"
          :options="optionsObject"
          :hideFiltersOption="hideFiltersOption"
          :useModals="useModals"
          :formatter="formatter"
          :sidebarFilters="sidebarFilters"
        />
      </div>
      <div v-else-if="shortcode == 'property-search'">
        <property-search
          v-if="propertiesArray[0] && propertiesArray[0].property_code"
          :properties="propertiesArray"
          :options="optionsObject"
          :hideFiltersOption="hideFiltersOption"
          :formatter="formatter"
          :showMap="hasMapData"
          :featuredSearchTerms="featuredSearchTermsObject"
        />
      </div>
    </v-main>
    <!-- Removed by request -->
    <!-- <v-footer class="renptress-shortcode-footer-wrapper">
      <v-row justify="center">
        <div
          class="rentpress-inherited-font-family text-caption renptress-shortcode-author-wrapper"
          @click="goToRentpress"
        >
          Created by RentPress
        </div>
      </v-row>
    </v-footer> -->
  </v-app>
</template>

<script>
// Shared
import singleFloorplanPage from "@shared/components/single-floorplan-page.vue";
import singlePropertyPage from "@shared/components/single-property-page.vue";
import floorplanSearch from "@shared/components/floorplan-search.vue";
import errorPage from "@shared/components/error.vue";

import neighborhoodMap from "./components/neighborhood-map.vue";
import propertySearch from "./components/property-search.vue";

export default {
  name: "App",
  data: function() {
    return {
      propertyObject: {},
      floorplanObject: {},
      floorplansArray: [],
      parentPropertiesArray: [],
      hideCommunityFilterOption: false,
      propertiesArray: [],
      optionsObject: {},
      cityObject: {},
      featuredSearchTermsObject: {},
      neighborhoodObject: {},
      showMaps: false,
      hideFiltersOption: false,
      useModals: false,
      useMapbox: false,
      sidebarFilters: false,
      hasGalleryShortcode: false,
      hideNeighborhood: false,
      formatter: new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      })
    };
  },
  computed: {
    userStyles() {
      return {
        "--user-font-fam":
          window
            .getComputedStyle(document.getElementsByTagName("BODY")[0], null)
            .getPropertyValue("font-family")
            .split(",")[0] ?? "Roboto",
        "--secondary-color": this.$vuetify.theme.themes.light.secondary,
        "--primary-color": this.$vuetify.theme.themes.light.primary
      };
    },
    hasMapData() {
      return (
        this.showMaps &&
        ((!this.useMapbox &&
          this.optionsObject.rentpress_google_maps_api_section_api_key !==
            "") ||
          (this.useMapbox &&
            this.optionsObject.rentpress_mapbox_maps_api_section_api_key !==
              "" &&
            this.propertyObject.property_latitude !== "" &&
            this.propertyObject.property_longitude !== ""))
      );
    }
  },
  props: {
    shortcode: String,
    floorplan: {
      type: String,
      default: ""
    },
    floorplans: {
      type: String,
      default: ""
    },
    parentproperties: {
      type: String,
      default: ""
    },
    hidecommunityfilter: {
      type: String,
      default: "false"
    },
    property: {
      type: String,
      default: ""
    },
    properties: {
      type: String,
      default: ""
    },
    usemap: {
      type: String,
      default: "false"
    },
    hidefilters: {
      type: String,
      default: "false"
    },
    use_modals: {
      type: String,
      default: "false"
    },
    use_mapbox: {
      type: String,
      default: "0"
    },
    sidebarfilters: {
      type: String,
      default: "false"
    },
    has_gallery_shortcode: {
      type: String,
      default: "false"
    },
    hide_neighborhood: {
      type: String,
      default: ""
    },
    neighborhood: {
      type: String,
      default: ""
    },
    city: {
      type: String,
      default: ""
    },
    featured_search_terms: {
      type: String,
      default: ""
    },
    options: String
  },
  components: {
    singleFloorplanPage,
    singlePropertyPage,
    floorplanSearch,
    propertySearch,
    neighborhoodMap,
    errorPage
  },
  methods: {
    goToRentpress() {
      window.open("https://rentpress.io", "_blank");
    }
  },
  mounted: function() {
    if (this.property !== "") {
      this.propertyObject = JSON.parse(this.property);
    }
    if (this.options !== "") {
      this.optionsObject = JSON.parse(this.options);
    }
    if (this.floorplan !== "") {
      this.floorplanObject = JSON.parse(this.floorplan);
    }
    if (this.floorplans !== "") {
      this.floorplansArray = JSON.parse(this.floorplans);
    }
    if (this.parentproperties !== "") {
      this.parentPropertiesArray = JSON.parse(this.parentproperties);
    }
    if (this.hidecommunityfilter == "true") {
      this.hideCommunityFilterOption = true;
    }
    if (this.properties !== "") {
      this.propertiesArray = JSON.parse(this.properties);
    }
    if (this.usemap == "true") {
      this.showMaps = true;
    }
    if (this.hidefilters == "true") {
      this.hideFiltersOption = true;
    }
    if (this.use_modals == "true") {
      this.useModals = true;
    }
    if (this.use_mapbox == "1") {
      this.useMapbox = true;
    }
    if (this.sidebarfilters == "true") {
      this.sidebarFilters = true;
    }
    if (this.has_gallery_shortcode == "true") {
      this.hasGalleryShortcode = true;
    }
    if (this.hide_neighborhood == "true") {
      this.hideNeighborhood = true;
    }
    if (this.city !== "") {
      this.cityObject = JSON.parse(this.city);
    }
    if (this.featured_search_terms !== "") {
      this.featuredSearchTermsObject = JSON.parse(this.featured_search_terms);
    }
    if (this.neighborhood !== "") {
      this.neighborhoodObject = JSON.parse(this.neighborhood);
    }
    this.$vuetify.theme.themes.light.primary = this.optionsObject
      .rentpress_accent_color_section_input
      ? this.optionsObject.rentpress_accent_color_section_input
      : "#3399FF";
    this.$vuetify.theme.themes.light.secondary = this.optionsObject
      .rentpress_secondary_accent_color_section_input
      ? this.optionsObject.rentpress_secondary_accent_color_section_input
      : "#3399FF";

    // Google analytics debug test code
    // (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    // (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    // m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    // })(window,document,'script','https://www.google-analytics.com/analytics_debug.js','ga');
    // window.ga('create', 'UA-166309024-1', 'auto');
    // window.ga('send', 'pageview');

    // Google Analytics
    function gtag() {
      window.dataLayer.push(arguments);
    }
    if (this.optionsObject.rentpress_google_analytics_api_section_tracking_id) {
      let trackingID = this.optionsObject
        .rentpress_google_analytics_api_section_tracking_id;
      window.dataLayer = window.dataLayer || [];
      gtag("js", new Date());
      gtag("config", trackingID);
      window.addEventListener("load", function() {
        window.ga("create", trackingID, "auto");
      });
    }
  }
};
</script>

<style lang="css">
.rentpress-shortcode-wrapper .v-application--wrap {
  min-height: 10vh !important;
}
.rentpress-shortcode-wrapper .rentpress-inherited-font-family {
  font-family: var(--user-font-fam);
  word-break: keep-all;
}

.rentpress-shortcode-wrapper .rentpress-floorplan-sidebar ::selection,
.rentpress-shortcode-wrapper
  .rentpress-top-floorplan-search-filter-wrapper
  ::selection,
.rentpress-shortcode-wrapper .rentpress-property-search-filters ::selection,
.rentpress-shortcode-wrapper .v-input ::selection {
  background: var(--primary-color);
  color: white;
}

.rentpress-shortcode-wrapper .rentpress-floorplan-sidebar input,
.rentpress-shortcode-wrapper
  .rentpress-top-floorplan-search-filter-wrapper
  input,
.rentpress-shortcode-wrapper .rentpress-property-search-filters input,
.rentpress-shortcode-wrapper .v-input input {
  box-shadow: none !important;
  border: none !important;
  background-color: transparent !important;
}

.rentpress-remove-link-decoration a {
  text-decoration: none;
}

.rentpress-no-wrap-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100%;
  display: inline-block;
}
</style>
