import Vue from "vue";
import App from "./App.vue";
import * as VueGoogleMaps from "vue2-google-maps";
import vuetify from "./plugins/vuetify";

Vue.config.productionTip = false;
const rootElement = document.querySelector("#rentpress-app");
let tempOptions = JSON.parse(rootElement.dataset.options);

Vue.use(VueGoogleMaps, {
  load: {
    key: tempOptions.rentpress_google_maps_api_section_api_key ?? ""
  },
  installComponents: true
});

Vue.mixin({
  methods: {
    sendGAEvent(category, action, label) {
      if (window.ga) {
        window.ga("send", "event", category, action, label);
      } else {
        return "nothing happened";
      }
    }
  }
});

new Vue({
  vuetify,

  render: h => {
    const context = {
      props: { ...rootElement.dataset }
    };
    return h(App, context);
  }
}).$mount("#rentpress-app");
