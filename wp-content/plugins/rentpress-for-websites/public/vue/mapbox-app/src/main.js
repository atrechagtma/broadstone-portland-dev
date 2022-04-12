import Vue from "vue";
import App from "./App.vue";
import vuetify from "./plugins/vuetify";

Vue.config.productionTip = false;
const rootElement = document.querySelector("#rentpress-app");

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
