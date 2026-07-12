(function () {
  "use strict";

  function evaluatePassword(password) {
    var rules = {
      length: password.length >= 8,
      upper: /[A-Z]/.test(password),
      lower: /[a-z]/.test(password),
      number: /\d/.test(password),
      symbol: /[^A-Za-z0-9]/.test(password)
    };

    var score = 0;
    Object.keys(rules).forEach(function (key) {
      if (rules[key]) {
        score += 1;
      }
    });

    var state = "empty";
    var text = "La nueva contraseña aún no ha sido evaluada.";
    var level = "Sin evaluar";

    if (password.length > 0 && score <= 2) {
      state = "weak";
      text = "Seguridad baja. Refuerza la contraseña antes de guardarla.";
      level = "Débil";
    } else if (password.length > 0 && score <= 4) {
      state = "medium";
      text = "Seguridad media. Aún puedes hacerla más sólida.";
      level = "Media";
    } else if (password.length > 0 && score === 5) {
      state = "strong";
      text = "Seguridad alta. Cumple con los criterios recomendados.";
      level = "Fuerte";
    }

    return {
      rules: rules,
      score: score,
      state: state,
      text: text,
      level: level
    };
  }

  function updateMeter(meterElement, result) {
    if (!meterElement) {
      return;
    }

    var segments = meterElement.querySelectorAll("span");
    meterElement.setAttribute("data-state", result.state);

    segments.forEach(function (segment, index) {
      segment.classList.toggle("is-active", index < result.score);
    });
  }

  function updateChecklist(listElement, result) {
    if (!listElement) {
      return;
    }

    Object.keys(result.rules).forEach(function (rule) {
      var item = listElement.querySelector('[data-rule="' + rule + '"]');
      if (!item) {
        return;
      }

      item.classList.toggle("is-valid", result.rules[rule]);
      item.classList.toggle("is-invalid", !result.rules[rule]);
    });
  }

  function updateStrengthText(textElement, result) {
    if (!textElement) {
      return;
    }

    textElement.textContent = result.text;
    textElement.classList.remove("text-danger", "text-warning", "text-success", "text-muted");

    if (result.state === "weak") {
      textElement.classList.add("text-danger");
      return;
    }

    if (result.state === "medium") {
      textElement.classList.add("text-warning");
      return;
    }

    if (result.state === "strong") {
      textElement.classList.add("text-success");
      return;
    }

    textElement.classList.add("text-muted");
  }

  function updateStrengthLabel(labelElement, result) {
    if (!labelElement) {
      return;
    }

    labelElement.textContent = result.level;
    labelElement.setAttribute("data-state", result.state);
  }

  function updateConfirmState(confirmInput) {
    var sourceSelector = confirmInput.getAttribute("data-password-confirm");
    var statusSelector = confirmInput.getAttribute("data-password-confirm-text");
    if (!sourceSelector || !statusSelector) {
      return;
    }

    var sourceInput = document.querySelector(sourceSelector);
    var statusElement = document.querySelector(statusSelector);

    if (!sourceInput || !statusElement) {
      return;
    }

    var passwordValue = sourceInput.value || "";
    var confirmValue = confirmInput.value || "";

    statusElement.classList.remove("text-danger", "text-success", "text-muted");

    if (confirmValue === "") {
      statusElement.textContent = "La confirmación debe coincidir exactamente.";
      statusElement.classList.add("text-muted");
      return;
    }

    if (passwordValue === confirmValue) {
      statusElement.textContent = "La confirmación coincide correctamente.";
      statusElement.classList.add("text-success");
      return;
    }

    statusElement.textContent = "La confirmación no coincide con la nueva contraseña.";
    statusElement.classList.add("text-danger");
  }

  function bindStrengthInput(input) {
    var meterSelector = input.getAttribute("data-password-strength-target");
    var textSelector = input.getAttribute("data-password-strength-text");
    var labelSelector = input.getAttribute("data-password-strength-label");
    var listSelector = input.getAttribute("data-password-checklist");

    var meterElement = meterSelector ? document.querySelector(meterSelector) : null;
    var textElement = textSelector ? document.querySelector(textSelector) : null;
    var labelElement = labelSelector ? document.querySelector(labelSelector) : null;
    var listElement = listSelector ? document.querySelector(listSelector) : null;

    function refresh() {
      var result = evaluatePassword(input.value || "");
      updateMeter(meterElement, result);
      updateChecklist(listElement, result);
      updateStrengthText(textElement, result);
      updateStrengthLabel(labelElement, result);

      document.querySelectorAll('[data-password-confirm="' + meterSelectorToInputSelector(input) + '"]').forEach(function (confirmInput) {
        updateConfirmState(confirmInput);
      });
    }

    input.addEventListener("input", refresh);
    input.addEventListener("change", refresh);
    refresh();
  }

  function meterSelectorToInputSelector(input) {
    if (input.id) {
      return "#" + input.id;
    }

    if (input.name) {
      return '[name="' + input.name + '"]';
    }

    return "";
  }

  function init() {
    document.querySelectorAll("[data-password-strength-input]").forEach(bindStrengthInput);
    document.querySelectorAll("[data-password-confirm]").forEach(function (confirmInput) {
      var refresh = function () {
        updateConfirmState(confirmInput);
      };

      confirmInput.addEventListener("input", refresh);
      confirmInput.addEventListener("change", refresh);
      refresh();
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
}());
