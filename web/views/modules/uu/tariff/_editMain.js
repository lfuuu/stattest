+function ($) {
	"use strict";
	$(function () {

		$("#tariff-is_autoprolongation").on("change", function () {
			let $this = $(this),
				$countOfValidityPeriod = $("#tariff-count_of_validity_period");

			// включить/выключить "кол-во продлений" в зависимости от "автопролонгация"
			if ($this.is(":checked")) {
				$countOfValidityPeriod.attr("readonly", "readonly");
				$countOfValidityPeriod.val("0");
			} else {
				$countOfValidityPeriod.removeAttr("readonly");
			}

			// включить/выключить "Пакет интернета сгорает через N месяцев" в зависимости от "автопролонгация" и "кол-во продлений"
			let $countOfCarryPeriod = $("#tariff-count_of_carry_period");
			if ($countOfValidityPeriod.val() === '0' && !$this.is(":checked")) {
				$countOfCarryPeriod.removeAttr("readonly");
			} else {
				$countOfCarryPeriod.attr("readonly", "readonly");
				$countOfCarryPeriod.val("0");
			}
		})
			.trigger('change');

		$("#tariff-count_of_validity_period").on("change", function () {
			// выполнить вышеуказанные проверки в зависимости от "кол-во продлений". В частности, включить/выключить "Пакет интернета сгорает через N месяцев"
			$("#tariff-is_autoprolongation").trigger('change');
		});

		$("#tariff-is_default").on("change", function() {

			let $this = $(this),
				$bundle = $("#tariff-is_bundle");

			if ($this.is(":checked")) {
				$bundle.prop( "checked", false );
				$bundle.prop( "disabled", true );
				$bundle.attr('readonly', 'readonly');
			} else {
				$bundle.removeAttr("readonly");
				$bundle.prop( "disabled", false );
			}

			if ($bundle.is(":checked")) {
				$this.prop( "checked", false );
				$this.prop( "disabled", true );
				$this.attr('readonly', 'readonly');
			} else {
				$this.removeAttr("readonly");
				$this.prop( "disabled", false );
			}

		})
			.trigger('change');

		$("#tariff-is_bundle").on("change", function () {
			$("#tariff-is_default").trigger('change');
		});

	})
}(jQuery);