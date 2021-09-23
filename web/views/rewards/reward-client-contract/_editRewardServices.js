+function ($) {
	"use strict";
	$(function () {
        $('[id^=rewardclientcontractservice]').change(function(event) {
            let id = $(event.target).attr('id').replace('rewardclientcontractservice-', '');
            id = id.split("-");
            if (id[1] === "once_only") {
                let $this = $(this),
                $percentageField = $("#rewardclientcontractservice-" + id[0] + "-percentage_once_only"),
                $minimalField = $("#rewardclientcontractservice-" + id[0] + "-percentage_of_minimal"),
                $feeField = $("#rewardclientcontractservice-" + id[0] + "-percentage_of_fee"),
                $periodType = $("#rewardclientcontractservice-" + id[0] + "-period_type"),
                $periodMonth = $("#rewardclientcontractservice-" + id[0] + "-period_month");

                if ($this.val()) {
                    $percentageField.attr("readonly", "readonly");
                    $percentageField.val("");
                    $minimalField.attr("readonly", "readonly");
                    $minimalField.val("");
                    $feeField.attr("readonly", "readonly");
                    $feeField.val("");
                    $periodType.attr("readonly", "readonly");
                    $periodType.val("");
                    $periodMonth.attr("readonly", "readonly");
                    $periodMonth.val("");
                } else {
                    $percentageField.removeAttr("readonly");
                    $minimalField.removeAttr("readonly");
                    $feeField.removeAttr("readonly");
                    $periodType.removeAttr("readonly");
                    $periodMonth.removeAttr("readonly");
                }
            } else if (id[1] === "percentage_once_only") {
                let $this = $(this),
                $onceOnlyField = $("#rewardclientcontractservice-" + id[0] + "-once_only");
        
                if ($this.val()) {
                    $onceOnlyField.attr("readonly", "readonly");
                    $onceOnlyField.val("");
                } else {
                    $onceOnlyField.removeAttr("readonly");
                }
            } else if (id[1] === "period_type") {
                let $this = $(this),
                $periodType = $("#rewardclientcontractservice-" + id[0] + "-period_type");
                
                let $periodMonth = $("#rewardclientcontractservice-" + id[0] + "-period_month");
                if ($this.val() === "always") {
                    $periodMonth.attr("readonly", "readonly");
                    $periodMonth.val("");
                } else {
                    $periodMonth.removeAttr("readonly");
                }
            }
        })
	})
}(jQuery);