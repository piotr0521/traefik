import AOS from "aos";
import ApexCharts from "apexcharts";
import Swiper, { Pagination } from "swiper";

function handleMobileMenu() {
  const menuBars = document.querySelectorAll(".nav-menubar");
  const menuMobile = document.querySelector(".menu-mobile");

  // click icon menubar on header mobile
  function toggleActiveClass() {
    menuBars.forEach(function (bar) {
      bar.classList.toggle("is-active");
    });
  }

  // toggle icon menubar and disable scroll when menu open
  menuBars.forEach(function (menuBar) {
    menuBar.addEventListener("click", function () {
      toggleActiveClass();
      menuMobile.classList.toggle("is-open");
      document.body.classList.toggle("overflow-hidden");
    });
  });
}

function initSwiper() {
  // review carousel on homepage
  const reviewCarousel = new Swiper(".review-carousel", {
    slidesPerView: 1,
    spaceBetween: 15,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    modules: [Pagination],
    breakpoints: {
      640: {
        slidesPerView: 2,
        spaceBetween: 15,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 20,
      },
    },
  });
}

function accordion() {
  const accordions = document.querySelectorAll(".accordion");

  accordions.forEach((accordion) => {
    const items = accordion.querySelectorAll(".accordion-item");

    items.forEach((item) => {
      const title = item.querySelector(".accordion-title");

      // add click event listener to each accordion title
      title.addEventListener("click", () => {
        // toggle active class to the clicked accordion item
        item.classList.toggle("is-active");

        // collapse other accordion items if they are active
        items.forEach((otherItem) => {
          if (otherItem !== item && otherItem.classList.contains("is-active")) {
            otherItem.classList.remove("is-active");
          }
        });
      });
    });
  });
}

function initAos() {
  AOS.init({
    offset: 200,
    duration: 600,
    once: true,
  });
}

function featuresChart() {
  const options = {
    chart: {
      type: "area",
      height: 450,
      toolbar: {
        show: false,
      },
      animations: {
        enabled: true,
        easing: "linear",
        dynamicAnimation: {
          speed: 1000, // set the animation speed to 1 seconds
        },
      },
    },
    series: [
      {
        name: "Data",
        data: [],
      },
    ],
    xaxis: {
      categories: [],
      labels: {
        show: false,
      },
    },
    yaxis: {
      max: 100,
      labels: {
        show: false,
      },
    },
    grid: {
      show: false,
    },
    colors: ["#B7D4FB"],
    fill: {
      type: "gradient",
      colors: ["#B7D4FB"],
      gradient: {
        opacityFrom: 0.7,
        opacityTo: 0.0,
      },
    },
    tooltip: {
      enabled: true,
    },
    stroke: {
      curve: "straight",
      width: 1,
    },
    dataLabels: {
      enabled: false,
    },
    forecastDataPoints: {
      count: 5,
    },
  };

  // Create the chart instance and render it
  const chartElement = document.querySelector("#chart");
  if (!chartElement) {
    return;
  }

  const chart = new ApexCharts(chartElement, options);
  chart.render();

  // Initialize the data array and moving average variables
  let data = Array.from({ length: 10 }, () => 0);
  let movingAvg = Array.from({ length: 10 }, () => 0);
  let sum = 0;

  // Update the chart every 1 second
  setInterval(() => {
    // Generate a new data point
    const newDataPoint = Math.floor(Math.random() * 100);

    // Update the data array and calculate the new moving average
    data.shift();
    data.push(newDataPoint);
    sum = sum - movingAvg[0] + newDataPoint;
    movingAvg.shift();
    movingAvg.push(sum / 10);

    // Update the chart
    chart.updateSeries([
      {
        data: movingAvg,
      },
    ]);
  }, 1000);
}

function pricingCard() {
  const cards = document.querySelectorAll(".card-pricing");

  cards.forEach((card) => {
    const tabs = card.querySelectorAll(".pricing-tabs .pricing-button");
    const contents = card.querySelectorAll(".tabs-content .pricing-content");

    tabs.forEach((tab) => {
      tab.addEventListener("click", (e) => {
        // remove the active class from all tabs and contents in this card
        tabs.forEach((tab) => {
          tab.classList.remove("tab-active");
        });
        contents.forEach((content) => {
          content.classList.remove("tab-open");
        });

        // add the active class to the clicked tab and corresponding content in this card
        const clickedTab = e.currentTarget;
        const targetContent = card.querySelector(clickedTab.getAttribute("data-tab-target"));
        clickedTab.classList.add("tab-active");
        targetContent.classList.add("tab-open");
      });
    });
  });
}

document.addEventListener("DOMContentLoaded", function () {
  handleMobileMenu();
  initSwiper();
  initAos();
  accordion();
  featuresChart();
  pricingCard();
});
