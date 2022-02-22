<!-- property search schema -->
<script type="application/ld+json">
{
	"@context": "https://schema.org",
      "@type": "WebSite",
      "url": "<?php echo(get_permalink()); ?>",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo(get_permalink()); ?>?search={search_term_string}",
        "query-input": "required name=search_term_string"
      }
}
</script>


