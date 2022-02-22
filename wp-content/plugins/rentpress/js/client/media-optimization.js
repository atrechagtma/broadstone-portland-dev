// image swapper functions start

function indexOfClosest(counts, goal) {
  count = [];
  for (var i = counts.length - 1; i >= 0; i--) {
    count.push( counts[i][1] );
  }
  return count.reduce((prev, curr) => Math.abs(curr - goal) < Math.abs(prev - goal) ? curr : prev);
}

function getDataAttributes(el) {
    var data = {};
    [].forEach.call(el.attributes, function(attr) {
        if (/^data-/.test(attr.name)) {
            var camelCaseName = attr.name.substr(5).replace(/-(.)/g, function ($0, $1) {
                return $1.toUpperCase();
            });
            data[camelCaseName] = attr.value;
        }
    });
    return data;
}

function changeImagesSrcClose(allImageSizes, finalImageWidth, mainImage) {
  for (var i = allImageSizes.length - 1; i >= 0; i--) {
    image = allImageSizes[i];
    if ( finalImageWidth == image[1] ) {
      mainImage.dataset.src = image[0] ;
    }
  }
}

function changeImagesSrcOver(allImageSizes, imageWith, imageHeight, mainImage) {
  allImageSizes = allImageSizes.sort(function(a, b){return a[1] - b[1]});
  for (var i = 0; i < allImageSizes.length; i++) {
    if ( Number( allImageSizes[i][1] ) >= imageWith && Number( allImageSizes[i][2] ) >= imageHeight ) {
      mainImage.dataset.src = allImageSizes[i][0] ;
      break;
    }
  }
}

function getImageSizes(image, bool) {
  //set up vars
  imageDefalts = [] ;
  allImageSizes = [];
  imageAttributes = getDataAttributes( image );
  for (let [key, image] of Object.entries( imageAttributes ) ) {
    imageHeight = image.split(",")[2] ;
    imageWith = image.split(",")[1] ;
    imageName = image.split(",")[0] ;

    allImageSizes.push( [ imageName, imageWith, imageHeight ] );
    imageDefalts.push(imageWith);
  }
  imageWith = image.clientWidth ;
  imageHeight = image.clientHeight ;
  imageFull = imageAttributes.full ;
  
  if ( allImageSizes.length && imageWith && bool ) {
    finalImageWidth = Number( indexOfClosest( allImageSizes, imageWith ) );
    changeImagesSrcClose(allImageSizes, finalImageWidth, image);
  } else {
    changeImagesSrcOver(allImageSizes, imageWith, imageHeight, image);
  }
}

function changeImagesSize(bool) {
  //get all images
  allImages = document.getElementsByTagName('img') ;
  for (var i = allImages.length - 1; i >= 0; i--) {
    //if image has data-sizes
    if ( getDataAttributes(allImages[i]).full ) {
      getImageSizes(allImages[i], bool);
    }
  }
}

// image swapper functions end
function rpLazyLoad() {
  var throttleTimeout;
  var rpLazyLoadElements = document.querySelectorAll(".rp-lazy");   

if(throttleTimeout) {
    clearTimeout(throttleTimeout);
}    
  

  throttleTimeout = setTimeout(function() {
      rpLazyLoadElements.forEach(function(element) {
        var elementOffsetTop = element.getBoundingClientRect().top;
        
          if( elementOffsetTop - 500 < (window.innerHeight + window.pageYOffset)) {
            if(element.dataset.src) {
              element.src = element.dataset.src;
            } else if(element.dataset.srcset) {
              element.srcset = element.dataset.srcset;
            }
            
              element.classList.remove('rp-lazy');
          }
      });
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {

  var rpLazyLoadElements = document.querySelectorAll(".rp-lazy");
  var throttleTimeout;
    
  document.addEventListener("scroll", rpLazyLoad);
  window.addEventListener("click", rpLazyLoad);
  window.addEventListener("load", rpLazyLoad);
  window.addEventListener("resize", rpLazyLoad);
  window.addEventListener("orientationChange", rpLazyLoad);

});