/**
 * Created by Andrey Morozov on 21.09.15.
 */

(function() {

    var analystics = {

        form: $('form.analytics-form'),
        addConditionButton: $('.analytycs-condition button'),
        conditionSelect: $('#condition-select'),
        conditionList: $('#conditionList table tbody'),
        parameters: [],

        addAggregationButton: $('.analytycs-aggregation button'),
        aggregationSelect: $('#aggregation-select'),
        aggregationList: $('#aggregationList table tbody'),
        parametersAggregation: [],

        errorBlock: $('form.analytics-form div.alert-danger'),

        initialize : function () {
            this.errorBlock.hide();
            this.setUpListeners();
        },

        setUpListeners: function () {

            var self = this;

            this.addConditionButton.on('click', function() {
                self.addConditionByParametr(self.conditionSelect.val(), 'condition');
            });
            this.conditionSelect.children().on('dblclick', function() {
                self.addConditionByParametr($(this).val(), 'condition');
            });

            this.addAggregationButton.on('click', function() {
                self.addConditionByParametr(self.aggregationSelect.val(), 'aggregation');
            });
            this.aggregationSelect.children().on('dblclick', function() {
                self.addConditionByParametr($(this).val(), 'aggregation');
            });

            this.form.on('submit', function() {

                var that = $(this),
                    url = that.attr('action'),
                    serializeArray = that
                        .find('select#groupby, [name*="group["], [name*="condition["], [name*="aggregation["], [name="_csrf"]')
                        .serializeArray();

                var data = {};
                $.each(serializeArray, function(i, field) {
                    data[field.name] = field.value;
                });

                $.ajax({
                    url: url,
                    type: 'post',
                    data: data,
                    success: function(response, textStatus, jqXHR) {
                         var response = JSON.parse(jqXHR.responseText);
                         if (response.res == 'ok') {
                             $("#report").html(response.data.toString());

                             $("#analyticsTabs li.disabled").removeClass('disabled');
                             $('#analyticsTabs a[href="#report"]').tab('show');
                         } else {
                            self.showError(response.data);
                         }
                    }
                });

                return false;
            });
        },

        showError: function(message) {
            this.errorBlock.html(message).show();
            var self = this;
            setTimeout(function() {
                self.errorBlock.fadeOut('normal');
            }, 5000);
        },

        addConditionByParametr: function(parameterId, type) {
            var self = this,
                errMsg = errMessages.condMsgError,
                url = '/analytics/default/add-condition',
                listArr = self.parameters;

            if (type == 'aggregation') {
                errMsg = errMessages.aggrMsgError;
                url = '/analytics/default/add-aggregation';
                listArr = self.parametersAggregation;
            }

            if (jQuery.inArray(parameterId, listArr) !== -1) {
                this.showError(errMsg);
                return;
            }
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: {parameterId: parameterId},
                success: function(response, textStatus, jqXHR){
                    if (response.res == 'ok') {
                        if (type == 'aggregation') {
                            self.aggregationList.append(response.data);
                        } else {
                            self.conditionList.append(response.data);
                        }
                        self.eventAfterAddCondition(parameterId, type);
                    } else {
                        self.showError(response.data);
                    }
                }
            });
        },
        eventAfterAddCondition: function(parameterId, type) {
            if (type == 'aggregation') {
                var conditionBlock = $("#parametr_aggregation_" + parameterId);

                this.parametersAggregation.push(parameterId);
                this.aggregationList.find("select").on('change', function () {
                    self.changeAggregationType(parameterId, $(this));
                });
            } else {
                var conditionBlock = $("#parametr_condition_" + parameterId),
                    select2Input = $("select.select2-bind");

                this.parameters.push(parameterId);
                this.bindSelect2(select2Input, select2Input.data("url"));
            }

            this.deleteButtonBindEvent();

            var self = this;
            conditionBlock.find("a.delete").on('click', function (event) {
                self.dropCondition(parameterId, type);
            });
        },
        bindSelect2: function(select2Input, ajaxUrl) {
            var $element = select2Input.select2({
                ajax: {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchTerm: params.term,
                            page: params.page
                        };
                    },
                    processResults: function (data, page) {
                        return {results: data};
                    },
                    cache: true
                }
            });

            var $request = $.ajax({
                url: ajaxUrl
            });

            $request.then(function (data) {
                for (var d = 0; d < data.length; d++) {
                    var item = data[d];
                    var option = new Option(item.text, item.id, true, true);
                    $element.append(option);
                }
                $element.trigger('change');
            });

            select2Input.removeClass('select2-input');
        },
        changeAggregationType: function(parameterId, select) {
            var input = select.parents('tr').find(".jsAggrAdditionalValue");
            if (select.val() == 'histogram') {
                input.show();
            } else {
                input.hide();
            }
        },
        deleteButtonBindEvent: function() {
            var lists = this.conditionList.add(this.aggregationList);
            lists.find('tr').unbind().bind({
                mouseenter: function(e) {
                    $(this).find('a.hidden-button').show();
                },
                mouseleave: function(e) {
                    $(this).find('a.hidden-button').hide();
                }
            });
        },
        dropCondition: function(parameterId, type) {
            if (type == 'condition') {
                this.parameters = jQuery.grep(this.parameters, function(value) {
                    return value != parameterId;
                });
                $("#parametr_condition_" + parameterId).remove();
            } else {
                this.parametersAggregation = jQuery.grep(this.parametersAggregation, function(value) {
                    return value != parameterId;
                });
                $("#parametr_aggregation_" + parameterId).remove();
            }
        }
    };
    analystics.initialize();
}());