class AccounttariffEdit

  country = null
  numberType = null
  regions = null
  didGroup = null
  numbersList = null
  numbersListSelectAllCheckbox = null
  numbersListClass = null
  numbersListOrderByField = null
  numbersListOrderByType = null
  numbersListMask = null

  # инициализация
  constructor: () ->
    setTimeout () =>
      @country = $('#voipCountryId').on('change', @onCountryChange)
      @numberType = $('#voipNumberType').on('change', @onNumberTypeOrRegionsChange)
      @regions = $('#voipRegions').on('change', @onNumberTypeOrRegionsChange)
      @didGroup = $('#voipDidGroup').on('change', @showNumbersList)

      @numbersListClass = $('#voipNumbersListClass').on('change', @showNumbersList)
      @numbersListOrderByField = $('#voipNumbersListOrderByField').on('change', @showNumbersList)
      @numbersListOrderByType = $('#voipNumbersListOrderByType').on('change', @showNumbersList)
      @numbersListMask = $('#voipNumbersListMask').on('change', @showNumbersList)

      @numbersList = $('#voipNumbersList')
      @numbersListSelectAll = $('#voipNumbersListSelectAll')
      @numbersListSelectAllCheckbox = @numbersListSelectAll.find('input').on('change', @selectAllNumbers)
    , 200 # Потому что select2 рендерит чуть позже. @todo

# при изменении страны
  onCountryChange: () =>
    countryVal = @country.val()
    if countryVal

      $.get '/uu/voip/get-number-types', {countryId: countryVal, isWithEmpty: true, format: 'options'}, (html) =>
        @numberType.html(html) # обновить значения
        @numberType.prop("disabled", false)
        @numberType.val('').trigger('change')

      $.get '/uu/voip/get-cities', {countryId: countryVal, isWithEmpty: true, format: 'options'}, (html) =>
        @regions.html(html) # обновить значения
        @regions.prop("disabled", false)
        @regions.val('').trigger('change')

    else
      @numberType.prop("disabled", true)
      @numberType.val('').trigger('change')

      @regions.prop("disabled", true)
      @regions.val('').trigger('change')

# при изменении типа или региона
  onNumberTypeOrRegionsChange: =>
    numberTypeVal = @numberType.val()
    regionsVal = @regions.val()

    if numberTypeVal == 'number' # для номера нужно выбрать город
      @regions.prop("disabled", false)
    else # для 7800, линии или неуказанного нельзя выбрать город
      @regions.prop("disabled", true)

    if regionsVal && numberTypeVal == 'number'
      $.get '/uu/voip/get-did-groups', {cityId: regionsVal, isWithEmpty: true, format: 'options'}, (html) =>
        @didGroup.html(html) # обновить значения
        @didGroup.prop("disabled", false)
        @didGroup.val('').trigger('change')
    else
      @didGroup.prop("disabled", true)
      @didGroup.val('').trigger('change')
    @showNumbersList()

# показать номера
# при изменении красивости или кол-ва колонок или сортировки
  showNumbersList: =>
    numberTypeVal = @numberType.val()
    regionsVal = @regions.val()
    didGroupVal = @didGroup.val()

    if regionsVal and numberTypeVal == 'number' # выбирать пока только для номера. Потом еще для 7800
      @numbersListClass.prop("disabled", false)
      @numbersListOrderByField.prop("disabled", false)
      @numbersListOrderByType.prop("disabled", false)
      @numbersListMask.prop("disabled", false)
    else
      @numbersListClass.prop("disabled", true)
      @numbersListOrderByField.prop("disabled", true)
      @numbersListOrderByType.prop("disabled", true)
      @numbersListMask.prop("disabled", true)

    if numberTypeVal == '7800' or numberTypeVal == 'line' or (numberTypeVal and regionsVal)
      $.get '/uu/voip/get-free-numbers', {
        cityId: regionsVal,
        didGroupId: didGroupVal,
        rowClass: @numbersListClass.val(),
        orderByField: @numbersListOrderByField.val(),
        orderByType: @numbersListOrderByType.val()
        mask: @numbersListMask.val()
        numberType: numberTypeVal
      }, (html) =>
        @numbersList.html(html) # обновить значения
        if @numbersList.find('input').length # есть чекбоксы - показать "выбрать все"
          @numbersListSelectAll.show()
        else
          @numbersListSelectAll.hide()

    else
      @numbersList.html('')
      @numbersListSelectAll.hide()

# выбрать все / снять выделение
  selectAllNumbers: =>
    isChecked = @numbersListSelectAllCheckbox.is(':checked')
    @numbersList.find('input').prop('checked', isChecked)

new AccounttariffEdit()