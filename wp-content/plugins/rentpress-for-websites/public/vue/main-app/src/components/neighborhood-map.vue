<template>
  <div>
    <iframe
      title="Map of the properties neighborhood"
      width="100%"
      height="500"
      frameborder="0"
      style="border: 0"
      :src="
        `https://www.google.com/maps/embed/v1/place?key=` +
        options.rentpress_google_maps_api_section_api_key +
        `&zoom=15&q=` +
        property.property_name +
        `,` +
        property.property_address +
        `,` +
        property.property_city +
        `,` +
        property.property_state
      "
    />
  </div>
</template>

<script>
export default {
  props: {
    property: Object,
    options: Object
  }
};
</script>
