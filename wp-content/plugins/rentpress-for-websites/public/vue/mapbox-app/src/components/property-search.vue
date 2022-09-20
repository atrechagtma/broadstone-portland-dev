<template class="rentpress-advanced-search-shortcode-property-search-wrapper">
  <div>
    <div v-show="!hideFiltersOption">
      <v-row justify="center" class="hidden-md-and-up pb-5">
        <v-col cols="11" sm="11">
          <v-btn block color="primary" @click="toggleMap = !toggleMap">
            <v-icon class="pr-4">mdi-tune</v-icon> Toggle Filters and Map
          </v-btn>
        </v-col>
      </v-row>
    </div>
    <div v-show="!hideFiltersOption && shouldShowMap">
      <v-row justify="center" class="py-0 rentpress-property-search-filters" align="center">
        <v-col xl="3" lg="3" md="4" sm="5" cols="auto">
          <v-combobox
            v-model="selectedTerms"
            :items="searchableTerms"
            auto-select-first
            chips
            clearable
            deletable-chips
            multiple
            solo
            label="Search by property, city, state, zip, or amenity"
            @change="updateParams"
          ></v-combobox>
        </v-col>
        <v-col cols="auto">
          <v-select
            solo
            clearable
            multiple
            chips
            deletable-chips
            label="Select Bedroom"
            :items="possibleBedrooms"
            v-model="selectedBedrooms"
            @change="updateParams"
          ></v-select>
        </v-col>
        <v-col cols="auto" v-if="priceRanges.length > 0">
          <v-select
            solo
            clearable
            label="Select Max Price"
            :items="priceRanges"
            v-model="selectedPrice"
            @change="updateParams"
          ></v-select>
        </v-col>
        <v-col
          cols="auto"
          v-if="
            featuredSearchTerms.pets ||
              featuredSearchTerms.propertyType ||
              featuredSearchTerms.featuredAmenities
          "
        >
          <v-menu :close-on-content-click="false">
            <template v-slot:activator="{ on }">
              <div class="rentpress-advanced-filters-more-dropdown" v-on="on">
                <v-select
                  solo
                  :label="
                    selectedFeaturedTermsLength > 0
                      ? selectedFeaturedTermsLength + ' Selected'
                      : 'More'
                  "
                  readonly
                  :class="
                    selectedFeaturedTermsLength > 0
                      ? 'primary--text'
                      : 'rentpress-light-grey-text'
                  "
                ></v-select>
              </div>
            </template>

            <v-card>
              <v-list>
                <div v-if="featuredSearchTerms.pets">
                  <v-list-item>
                    <v-list-item-title>Pets</v-list-item-title>
                  </v-list-item>
                  <div
                    v-for="(petType, index) in featuredSearchTerms.pets"
                    :key="petType + index"
                  >
                    <div v-if="petType">
                      <v-list-item>
                        <v-list-item-action>
                          <v-checkbox
                            v-model="selectedPets"
                            :value="petType"
                            color="primary"
                            @change="updateParams"
                          ></v-checkbox>
                        </v-list-item-action>
                        <v-list-item-title v-html="petType"></v-list-item-title>
                      </v-list-item>
                    </div>
                  </div>
                  <v-divider
                    v-if="
                      featuredSearchTerms.propertyType ||
                        featuredSearchTerms.featuredAmenities
                    "
                  ></v-divider>
                </div>
                <div v-if="featuredSearchTerms.propertyType">
                  <v-list-item>
                    <v-list-item-title>Property Type</v-list-item-title>
                  </v-list-item>
                  <div
                    v-for="(propType,
                    index) in featuredSearchTerms.propertyType"
                    :key="propType + index"
                  >
                    <div v-if="propType">
                      <v-list-item>
                        <v-list-item-action>
                          <v-checkbox
                            v-model="selectedTypes"
                            :value="propType"
                            color="primary"
                            @change="updateParams"
                          ></v-checkbox>
                        </v-list-item-action>
                        <v-list-item-title
                          v-html="propType"
                        ></v-list-item-title>
                      </v-list-item>
                    </div>
                  </div>
                  <v-divider
                    v-if="featuredSearchTerms.featuredAmenities"
                  ></v-divider>
                </div>
                <div v-if="featuredSearchTerms.featuredAmenities">
                  <v-list-item>
                    <v-list-item-title>Featured Amenities</v-list-item-title>
                  </v-list-item>
                  <div
                    v-for="(ftAmenity,
                    index) in featuredSearchTerms.featuredAmenities"
                    :key="ftAmenity + index"
                  >
                    <div v-if="ftAmenity">
                      <v-list-item>
                        <v-list-item-action>
                          <v-checkbox
                            v-model="selectedAmenities"
                            :value="ftAmenity"
                            color="primary"
                            @change="updateParams"
                          ></v-checkbox>
                        </v-list-item-action>
                        <v-list-item-title
                          v-html="ftAmenity"
                        ></v-list-item-title>
                      </v-list-item>
                    </div>
                  </div>
                </div>
              </v-list>
            </v-card>
          </v-menu>
        </v-col>
        <v-col v-if="filteredProperties.length == 0" cols="auto">
          <v-btn @click="resetSearch" color="primary">Reset</v-btn>
        </v-col>
      </v-row>
    </div>

    <div v-if="filteredProperties.length > 0">
      <v-row dense justify="center">
        <v-col
          v-show="hasMapData && shouldShowMap"
          v-resize="onResize"
          order-sm="first"
          order-md="last"
          order-lg="last"
          order-xl="last"
          sm="12"
          md="6"
          lg="6"
          xl="6"
          style="padding: 0px"
        >
          <div
            id="mapContainer"
            :style="`width: 100%; height: ` + mapSize + `; min-width: 200px;`"
          ></div>
        </v-col>
        <v-col
          order-sm="last"
          order-md="first"
          order-lg="first"
          order-xl="first"
          sm="12"
          :md="hasMapData ? '6' : '12'"
          :lg="hasMapData ? '6' : '12'"
          :xl="hasMapData ? '6' : '12'"
          style="background-color: #f7f7f7"
        >
          <div style="margin: auto">
            <v-row
              v-if="!hideFiltersOption"
              justify="space-between"
              align="center"
              no-gutters
              class="px-4"
            >
              <v-col xl="6" lg="6" md="6" sm="6" cols="12" class="text-center">
                Showing <strong>{{ matchingProperties }}</strong> Matching
                Properties
              </v-col>
              <v-spacer></v-spacer>
              <v-col
                xl="4"
                lg="5"
                md="6"
                sm="6"
                cols="8"
                class="text-center pt-7"
              >
                <v-select
                  solo
                  v-model="selectedSort"
                  :items="possibleSorts"
                ></v-select>
              </v-col>
            </v-row>
            <div
              :class="
                hasMapData && shouldShowMap
                  ? 'rentpress-card-container rentpress-map-sidebar-scroll-with-map'
                  : 'rentpress-card-container rentpress-map-sidebar-scroll-no-map'
              "
            >
              <div
                class="property-card-wrapper"
                v-for="(property, propertyIndex) in filteredProperties"
                :key="property.property_code"
              >
                <div
                  v-if="
                    showMoreProperties || propertyIndex < propertyDisplayLimiter
                  "
                >
                  <property-card
                    :property="property"
                    :options="options"
                    :formatter="formatter"
                  />
                </div>
              </div>
            </div>
            <v-row
              v-if="filteredProperties.length > propertyDisplayLimiter"
              justify="center"
            >
              <v-col cols="auto">
                <v-btn
                  v-if="!showMoreProperties"
                  color="primary"
                  @click="showMoreProperties = true"
                  >Display more results</v-btn
                >
                <v-btn
                  v-if="showMoreProperties"
                  color="primary"
                  @click="showMoreProperties = false"
                  >Display fewer results</v-btn
                >
              </v-col>
            </v-row>
          </div>
        </v-col>
      </v-row>
      <!-- <script v-html="jsonld" type="application/ld+json"></script> -->
    </div>
    <div
      v-else
      class="rentpress-advanced-search-shortcode-property-search-no-properties-container"
    >
      <v-row justify="center" class="text-center">
        <v-col cols="auto">
          <p
            class="rentpress-inherited-font-family primary--text text-h4 font-weight-black"
          >
            No Apartments Found
          </p>
          <p class="rentpress-inherited-font-family text-subtitle-1">
            For more information, please
            <a @click="navigateTo(contactLink)" class="primary--text">
              Contact Us.
            </a>
          </p>
        </v-col>
      </v-row>
    </div>
    <script v-html="jsonld" type="application/ld+json"></script>
  </div>
</template>

<script>
import propertyCard from "@shared/components/property-card.vue";
import "mapbox-gl/dist/mapbox-gl.css";
import Mapbox from "mapbox-gl";

export default {
  name: "property-search",
  components: { propertyCard },
  data() {
    return {
      windowHorizontalSize: 0,
      accessToken: this.options.rentpress_mapbox_maps_api_section_api_key,
      mapStyle: "mapbox://styles/mapbox/streets-v11",
      showMoreProperties: false,
      propertyDisplayLimiter: 10,
      onlyShowBed: this.options.requested_beds,
      onlyMaxPrice: this.options.max_price,
      onlyAvailable: this.options.only_available,
      goodValues: false,
      toggleMap: false,
      mapSize: "90vh",
      contactLink: window.location.host + "/contact",
      latitudes: [],
      longitudes: [],
      avgLat: 38.3198607,
      avgLong: -76.13,
      highLat: 38.3198607,
      highLong: -76.13,
      lowLat: 38.3198607,
      lowLong: -76.13,
      map: {},
      markers: [],
      hasWaited: false,
      searchableTerms: [],
      selectedTerms: [],
      selectedAmenities: [],
      selectedPets: [],
      selectedTypes: [],
      // possibleAmenities: [],
      // possiblePets: [],
      // possibleTypes: [],
      possibleBedrooms: [],
      selectedBedrooms: [],
      dropdownSelector: [
        {
          header: "Pets"
        },
        {
          text: "Cat",
          value: "Cat"
        },
        {
          text: "Dog",
          value: "Dog"
        }
      ],
      possibleSorts: ["Rent: Low to High", "Rent: High to Low"],
      selectedSort: "Rent: Low to High",
      priceRanges: [],
      selectedPrice: Number,
      infoWindowPos: {
        lat: 0,
        lng: 0
      },
      infoWinOpen: false,
      infoOptions: {
        pixelOffset: {
          width: 0,
          height: -10
        }
      },
      activeProperty: {},
      animationSelection: 2,
      states: {
        AL: "Alabama",
        AK: "Alaska",
        AZ: "Arizona",
        AR: "Arkansas",
        CA: "California",
        CO: "Colorado",
        CT: "Connecticut",
        DE: "Delaware",
        DC: "District of Columbia",
        FL: "Florida",
        GA: "Georgia",
        HI: "Hawaii",
        ID: "Idaho",
        IL: "Illinois",
        IN: "Indiana",
        IA: "Iowa",
        KS: "Kansas",
        KY: "Kentucky",
        LA: "Louisiana",
        ME: "Maine",
        MD: "Maryland",
        MA: "Massachusetts",
        MI: "Michigan",
        MN: "Minnesota",
        MS: "Mississippi",
        MO: "Missouri",
        MT: "Montana",
        NE: "Nebraska",
        NV: "Nevada",
        NH: "New Hampshire",
        NJ: "New Jersey",
        NM: "New Mexico",
        NY: "New York",
        NC: "North Carolina",
        ND: "North Dakota",
        OH: "Ohio",
        OK: "Oklahoma",
        OR: "Oregon",
        PA: "Pennsylvania",
        RI: "Rhode Island",
        SC: "South Carolina",
        SD: "South Dakota",
        TN: "Tennessee",
        TX: "Texas",
        UT: "Utah",
        VT: "Vermont",
        VA: "Virginia",
        WA: "Washington",
        WV: "West Virginia",
        WI: "Wisconsin",
        WY: "Wyoming"
      },
      jsonld: {
        // property search schema
        "@context": "https://schema.org",
        "@type": "WebSite",
        url: window.location.origin,
        potentialAction: {
          "@type": "SearchAction",
          target: {
            "@type:": "EntryPoint",
            urlTemplate:
              window.origin +
              window.location.pathname +
              "?search={search_term_string}"
          },
          "query-input": "name=search_term_string"
        },
        breadcrumb: [
          {
            itemListElement: [
              {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                itemListElement: [
                  {
                    "@type": "ListItem",
                    position: 1,
                    name: this.options.site_name + " Home",
                    item: this.options.site_url
                  },
                  {
                    "@type": "ListItem",
                    position: 2,
                    name: document.title,
                    item: window.location.origin + window.location.pathname
                  }
                ]
              }
            ]
          }
        ] // end breadcrumb
      }
    };
  },
  props: {
    properties: Array,
    options: Object,
    featuredSearchTerms: Object,
    hideFiltersOption: {
      type: Boolean,
      default: false
    },
    showMap: {
      type: Boolean,
      default: false
    },
    formatter: Intl.NumberFormat
  },
  computed: {
    filteredProperties() {
      if (this.possibleBedrooms.length === 0) {
        this.setUpDefaultValues(this.properties);
      }
      var props = this.properties.filter(property => {
        if (this.shouldPropertyBeRemovedBasedOnShortcodeSettings(property)) {
          return false;
        }
        if (this.selectedBedrooms.length > 0) {
          for (var i = this.selectedBedrooms.length - 1; i >= 0; i--) {
            if (
              !JSON.parse(property.property_bed_types).includes(
                this.selectedBedrooms[i]
              )
            ) {
              return false;
            }
          }
        }

        if (this.selectedPrice && this.selectedPrice != "") {
          let price = parseInt(this.selectedPrice.replace(/[^0-9]/g, ""));
          if (
            price < property.property_rent_type_selection_cost ||
            property.property_rent_type_selection_cost < 100
          ) {
            return false;
          }
        }

        return true;
      });

      props = props.filter(property => {
        let selectedTermsArray = [
          ...this.selectedTerms,
          ...this.selectedAmenities,
          ...this.selectedPets,
          ...this.selectedTypes
        ];

        // can ignore everything if no filters have data
        if (selectedTermsArray.length === 0) {
          return true;
        }

        const propertyTerms = [
          property.property_name,
          property.property_zip,
          property.property_state,
          this.states[property.property_state]
        ];
        if (property.property_additional_keywords) {
          propertyTerms.push(
            ...property.property_additional_keywords.split(",")
          );
        }

        if (property.property_terms) {
          propertyTerms.push(...JSON.parse(property.property_terms));
        }
        // get the difference between the arrays, if any value is left, that means the property doea not contain that term
        selectedTermsArray = selectedTermsArray.filter(
          term => !propertyTerms.includes(term)
        );

        return selectedTermsArray.length === 0;
      });

      if (this.selectedSort === "Rent: Low to High") {
        props.sort((a, b) =>
          parseInt(a.property_rent_type_selection_cost) >
          parseInt(b.property_rent_type_selection_cost)
            ? 1
            : -1
        );
      } else {
        props.sort((a, b) =>
          parseInt(a.property_rent_type_selection_cost) <
          parseInt(b.property_rent_type_selection_cost)
            ? 1
            : -1
        );
      }

      let visiblePropCodes = [];
      props.forEach(prop => {
        visiblePropCodes.push(prop.property_code);
      });

      this.updateMap(visiblePropCodes);
      this.updatePossibleFilters(props);

      return props;
    },
    matchingProperties() {
      if (
        !this.showMoreProperties &&
        this.filteredProperties.length > this.propertyDisplayLimiter
      ) {
        return (
          this.propertyDisplayLimiter +
          " out of " +
          this.filteredProperties.length
        );
      }
      return this.filteredProperties.length;
    },
    hasMapData() {
      return (
        this.showMap &&
        this.options.rentpress_mapbox_maps_api_section_api_key &&
        this.latitudes.length &&
        this.longitudes.length
      );
    },
    shouldShowMap() {
      return this.toggleMap || this.windowHorizontalSize >= 960;
    },
    selectedFeaturedTermsLength() {
      return (
        this.selectedPets.length +
        this.selectedAmenities.length +
        this.selectedTypes.length
      );
    }
  },
  methods: {
    onResize() {
      this.windowHorizontalSize = window.innerWidth;
      this.mapSize = this.windowHorizontalSize >= 960 ? "85vh" : "40vh";
    },
    navigateTo(newLocation) {
      window.location = "http://" + newLocation;
    },
    resetSearch() {
      this.selectedTerms = [];
      this.selectedBedrooms = [];
      this.selectedPrice = null;
      this.selectedAmenities = [];
      this.selectedPets = [];
      this.selectedTypes = [];
      this.updateParams();
    },
    updateParams() {
      const url = new URL(
        document.location.origin + document.location.pathname
      );
      let bedString = "N/A";
      let searchString = "N/A";
      let selectedPriceString = "N/A";
      let petString = "N/A";

      if (this.selectedPrice) {
        selectedPriceString = this.selectedPrice.replace(/[^0-9]/g, "");
        url.searchParams.set("price", selectedPriceString);
      }
      if (this.selectedBedrooms) {
        this.selectedBedrooms.forEach(a =>
          url.searchParams.append("bedrooms", a)
        );
        bedString = this.selectedBedrooms.join(", ");
      }
      if (this.selectedTerms) {
        this.selectedTerms.forEach(a => url.searchParams.append("search", a));
        searchString = this.selectedTerms.join(", ");
      }
      if (this.selectedAmenities) {
        this.selectedAmenities.forEach(a =>
          url.searchParams.append("amenity", a)
        );
      }
      if (this.selectedPets) {
        this.selectedPets.forEach(a => url.searchParams.append("pet", a));
        petString = this.selectedPets.join(", ");
      }
      if (this.selectedTypes) {
        this.selectedTypes.forEach(a => url.searchParams.append("type", a));
      }
      window.history.pushState({}, "", url);

      this.sendGAEvent(
        "Property Search",
        "filter",
        "Search Text: " +
          searchString +
          " - Bedrooms: " +
          bedString +
          " - Max Price: " +
          selectedPriceString +
          " - Pets: " +
          petString
      );
    },
    inPriceMatrix(matrix) {
      this.goodValues = false;
      Object.values(matrix).forEach(bedType => {
        if (
          bedType.price &&
          bedType.price > 100 &&
          bedType.price < this.onlyMaxPrice
        ) {
          this.goodValues = true;
        }
      });
      return this.goodValues;
    },
    shouldPropertyBeRemovedBasedOnShortcodeSettings(property) {
      var property_availability_matrix = JSON.parse(
        property.property_availability_matrix
      );
      // if they want specific bed type, this is also a guard function keeping the logic below all working without redundancy
      if (
        this.onlyShowBed &&
        property_availability_matrix[this.onlyShowBed + "bed"] === undefined
      ) {
        return true;
      }
      // if they have a specific bed type in mind and are looking for only available, we combine the logic
      if (
        this.onlyShowBed &&
        this.onlyAvailable &&
        !property_availability_matrix[this.onlyShowBed + "bed"].available
      ) {
        return true;
      }
      // if they only want available properties, then remove anything that is not
      if (this.onlyAvailable && property.property_available_floorplans < 1) {
        return true;
      }

      // if they have a specific max price and a specific floorplan in mind, check that floorplan type
      // otherwise if they just care about max price, gotta loop through each to see if one works
      if (
        this.onlyShowBed &&
        this.onlyMaxPrice &&
        property_availability_matrix[this.onlyShowBed + "bed"].price >
          this.onlyMaxPrice
      ) {
        return true;
      } else if (
        this.onlyMaxPrice &&
        !this.inPriceMatrix(property_availability_matrix)
      ) {
        return true;
      }
      return false;
    },
    updatePossibleFilters(props) {
      var bed_types = [];
      var searchTerms = [];
      var availablePrices = [];
      for (var y = props.length - 1; y >= 0; y--) {
        // remove property from page if it is filtered out by shortcode before its values get added to searchable terms
        if (this.shouldPropertyBeRemovedBasedOnShortcodeSettings(props[y])) {
          continue;
        }

        // Set up all possible bedrooms
        var property_bed_types = JSON.parse(props[y].property_bed_types);
        if (property_bed_types) {
          bed_types = [...bed_types, ...property_bed_types];
        }

        // Set up all possible search terms
        var terms = JSON.parse(props[y].property_terms);
        var additionalKeywords = props[y].property_additional_keywords
          ? props[y].property_additional_keywords.split(",")
          : [];

        if (terms) {
          searchTerms = [
            ...searchTerms,
            ...terms,
            ...additionalKeywords,
            props[y].property_zip,
            props[y].property_state,
            this.states[props[y].property_state],
            props[y].property_name
          ];
        }

        // Set up all possible price selections
        if (
          props[y].property_rent_type_selection_cost &&
          props[y].property_rent_type_selection_cost > 100
        ) {
          availablePrices = [
            ...availablePrices,
            parseInt(props[y].property_rent_type_selection_cost)
          ];
        }
      }
      // Set up bed types checkboxes
      this.possibleBedrooms = bed_types
        .filter((item, pos) => bed_types.indexOf(item) === pos)
        .sort();
      this.possibleBedrooms = this.possibleBedrooms.map(bedType => {
        if (bedType == 0) {
          return {
            text: "Studio",
            value: 0
          };
        } else {
          return {
            text: bedType + " Bed",
            value: bedType
          };
        }
      });
      // Set up all searchable terms
      this.searchableTerms = searchTerms
        .filter((item, pos) => searchTerms.indexOf(item) === pos)
        .sort((a, b) => {
          let aStartsWith = a.charAt(0);
          let bStartsWith = b.charAt(0);
          if (/^\d/.test(aStartsWith) && !/^\d/.test(bStartsWith)) {
            return 1;
          }
          if (!/^\d/.test(aStartsWith) && /^\d/.test(bStartsWith)) {
            return -1;
          }
          return a > b ? 1 : -1;
        });

      // Set up price selector by finding min and incrementing until you reach max
      const urlParams = new URLSearchParams(window.location.search);
      if (availablePrices.length > 0) {
        var minumumPrice = Math.ceil(Math.min(...availablePrices) / 50) * 50;
        var maximumPrice = urlParams.get("price")
          ? Math.floor(
              Math.max(...availablePrices, parseInt(urlParams.get("price"))) /
                50
            ) * 50
          : Math.floor(Math.max(...availablePrices) / 50) * 50;
        this.priceRanges = [this.formatter.format(minumumPrice)];
        while (minumumPrice < maximumPrice) {
          this.priceRanges.push(this.formatter.format((minumumPrice += 50)));
        }
      }
    },
    setUpDefaultValues(props) {
      this.updatePossibleFilters(props);

      for (var y = props.length - 1; y >= 0; y--) {
        // Set all location data
        if (props[y].property_latitude && props[y].property_longitude) {
          this.latitudes.push(parseFloat(props[y].property_latitude));
          this.longitudes.push(parseFloat(props[y].property_longitude));
        }
      }

      // Look at url params to set up data binds with url content
      const urlParams = new URLSearchParams(window.location.search);
      this.selectedTerms = urlParams.getAll("search");
      this.selectedAmenities = urlParams.getAll("amenity");
      this.selectedPets = urlParams.getAll("pet");
      this.selectedTypes = urlParams.getAll("type");
      urlParams
        .getAll("bedrooms")
        .forEach(a => this.selectedBedrooms.push(parseInt(a)));
      let price = urlParams.get("price");
      this.selectedPrice =
        price != null && Number.isInteger(Number(price))
          ? this.formatter.format(price)
          : "";
    },
    setUpMap() {
      this.lowLat = Math.min(...this.latitudes);
      this.lowLong = Math.min(...this.longitudes);
      this.highLat = Math.max(...this.latitudes);
      this.highLong = Math.max(...this.longitudes);
      this.avgLat =
        this.latitudes.reduce((a, b) => a + b) / this.latitudes.length;
      this.avgLong =
        this.longitudes.reduce((a, b) => a + b) / this.longitudes.length;

      Mapbox.accessToken = this.accessToken;

      this.map = new Mapbox.Map({
        container: "mapContainer",
        style: "mapbox://styles/mapbox/streets-v11",
        center: [this.avgLong, this.avgLat],
        zoom: 12,
        maxBounds: [
          [this.lowLong - 0.3, this.lowLat - 0.3],
          [this.highLong + 0.3, this.highLat + 0.3]
        ]
      });

      this.map.fitBounds([
        [this.lowLong - 0.02, this.lowLat - 0.02],
        [this.highLong + 0.02, this.highLat + 0.02]
      ]);

      this.map.on("load", () => {
        this.properties.forEach(prop => {
          if (prop.property_latitude && prop.property_longitude) {
            const popup = new Mapbox.Popup({ offset: 25 }).setHTML(
              `${prop.property_name}`
            );

            const marker = new Mapbox.Marker({
              color: this.$vuetify.theme.themes.light.primary
            })
              .setLngLat([prop.property_longitude, prop.property_latitude])
              .setPopup(popup) // sets a popup on this marker
              .addTo(this.map);
            this.markers.push({
              code: prop.property_code,
              markerObject: marker
            });
            // this.markers[prop.property_code] = marker;
          }
        });
      });
    },
    updateMap(visiblePropCodes) {
      // remove all markers
      // make new json source
      if (Object.keys(this.map).length !== 0 && this.map.loaded()) {
        this.markers.forEach(thisMarker => {
          if (visiblePropCodes.includes(thisMarker.code)) {
            thisMarker.markerObject.getElement().style.display = "block";
          } else {
            thisMarker.markerObject.getElement().style.display = "none";
          }
        });
      }
    }
  },
  mounted() {
    if (this.hasMapData) {
      this.setUpMap();
    }
  },
  created() {
    if (this.showMap) {
      this.mapbox = Mapbox;
    }
    this.onResize();
  }
};
</script>

<style>
.rentpress-advanced-filters-more-dropdown label {
  color: unset !important;
}

.rentpress-map-sidebar-scroll-with-map {
  display: grid;
  justify-content: center;
  justify-items: center;
  grid-template-columns: repeat(2, minmax(300px, 410px));
  gap: 2em;
  width: 100%;
  max-height: 66vh;
  overflow-y: auto;
  padding: 0em 1em 1em 1em;
}

@media (max-width: 1350px) {
  .rentpress-map-sidebar-scroll-with-map {
    grid-template-columns: minmax(300px, 410px);
  }
}

@media (min-width: 2000px) {
  .rentpress-map-sidebar-scroll-with-map {
    grid-template-columns: repeat(3, minmax(300px, 410px));
  }
}

.rentpress-light-grey-text label {
  color: rgb(0, 0, 0, 0.6) !important;
}

.rentpress-map-sidebar-scroll-with-map .property-card-wrapper {
  width: 100%;
}

.rentpress-map-sidebar-scroll-no-map {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  justify-items: center;
  gap: 2em;
}

.rentpress-map-sidebar-scroll-no-map > * {
  flex: 1 1 100%;
  max-width: clamp(300px, 100%, 410px);
}
</style>
