import Glide from "@glidejs/glide";

export function GlideModule() {
  if (!document.getElementById("glide")) {
    return;
  }
  var glideMarket = new Glide(".glide-market", {
    type: "slider",
    autoplay: false,
    hoverpause: true,
    perView: 1,
    breakpoints: {
      1024: {
        perView: 1,
      },
      600: {
        perView: 1,
      },
    },
  });
  var glideTop = new Glide(".glide-top", {
    type: "slider",
    autoplay: 5000,
    hoverpause: true,
    perView: 1,
    keyboard: false,
    breakpoints: {
      1024: {
        perView: 1,
      },
      600: {
        perView: 1,
      },
    },
  });
  glideMarket.mount();
  glideTop.mount();
}
GlideModule();
