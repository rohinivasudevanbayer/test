$(function () {
  //=====================================================================================================================
  // Helper functions for url query string
  //=====================================================================================================================
  window.QueryString = {
    get: function () {
      var queryParams = this.extract(document.location.search);
      delete queryParams["lang"];
      return queryParams;
    },
    extract: function (queryString) {
      var queryParams = {};
      if (queryString.substr(0, 1) === "?") {
        queryString = queryString.substr(1);
      }
      $.each(queryString.split("&"), function (c, q) {
        if (q.length) {
          var i = q.split("=");
          var key = i[0].toString();
          var value = i[1].toString();
          if (key.substr(key.length - 2, 2) === "[]") {
            key = key.substr(0, key.length - 2);
            if (!queryParams[key]) {
              queryParams[key] = [];
            }
            queryParams[key].push(value);
          } else {
            queryParams[key] = value;
          }
        }
      });
      return queryParams;
    },
    create: function (queryParams) {
      var paramNames = Object.keys(queryParams);
      var queryString = "";
      if (paramNames.length) {
        paramNames.forEach(function (paramName) {
          if (queryString.length === 0) {
            queryString = "?";
          } else {
            queryString += "&";
          }
          if ($.isArray(queryParams[paramName])) {
            queryString +=
              paramName +
              "[]=" +
              queryParams[paramName].join("&" + paramName + "[]=");
          } else {
            queryString += paramName + "=" + queryParams[paramName];
          }
        });
      }
      return queryString;
    }
  };

  //=====================================================================================================================
  // Language Select Dropdown Event
  //=====================================================================================================================
  $(".language-select").on("change", function () {
    var url = window.location.href.split("?")[0];
    var params = QueryString.get();
    params["lang"] = $(this).val();
    window.location.href = url + QueryString.create(params);
  });

  //=====================================================================================================================
  // Modal Ajax Request
  //=====================================================================================================================
  var modalCache = {};
  $('[data-toggle="ajaxModal"]').on("click", function (e) {
    if (e.ctrlKey) {
      return;
    }
    e.preventDefault();

    var $this = $(this);
    var remoteUrl = $this.data("remote") || $this.attr("href");
    var modalName = $this.data("target") || "defaultmodal";
    if (modalName.substr(0, 1) === "#") {
      modalName = modalName.substr(1);
    }
    var isStatic = !!$this.data("static");

    var $modal = $(
      window.fromPhp["modalStart"].replace(/###modalName###/g, modalName) +
        window.fromPhp["loadingNotification"] +
        window.fromPhp["modalEnd"]
    );

    $("body").append($modal);
    $modal.modal();

    if (isStatic && modalCache.hasOwnProperty(remoteUrl)) {
      $(".modal-content", $modal).html(modalCache[remoteUrl]);
    } else {
      $(".modal-content", $modal).load(remoteUrl, function () {
        modalCache[remoteUrl] = $(".modal-content", $modal).html();
      });
    }

    $modal.on("hidden.bs.modal", function (e) {
      $(this).remove();
    });
  });

  //=====================================================================================================================
  // Description for input fields
  //=====================================================================================================================
  $(".info-desc-btn").tooltip();
});
