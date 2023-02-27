;(function ($) {
  if (typeof acf.add_action === 'undefined') {
    return
  }

  const tinycolor = require('tinycolor2')

  /**
   * The hook below is fired when initializing
   * new or existing editor palette fields.
   *
   * @param {jQuery} element
   */
  acf.add_action('ready append', function (element) {
    acf.get_fields({ type: 'editor_palette' }, element).each(function () {
      $(this)
        .find('.toggle-color-palette')
        .click(() => {
          $(this).find('.color-palette-wrapper').toggleClass('hidden')
        })

      $(document).click(function (event) {
        if (
          !$(event.target).is('.color-palette-wrapper') &&
          !$(event.target).is('.components-circular-option-picker__swatches') &&
          !$(event.target).is('.color-palette-wrapper-title') &&
          !$(event.target).parent().is('li') &&
          !$(event.target)
            .parent()
            .hasClass('components-circular-option-picker')
        ) {
          $('.color-palette-wrapper').addClass('hidden')
        }
      })

      const onChange = () => {
        $(this).find('.component-color-indicator').removeClass('transparent-bg')

        let current = $(this).find('.acf-input li input + label.is-pressed')
        let selected = $(this).find('.acf-input li input:checked + label')

        if ($(event.target).parent().find('label').hasClass('is-pressed')) {
          current.removeClass('is-pressed').parent().find('svg').remove()
          event.stopPropagation()
          event.preventDefault()
          onClear()
          return
        }
        if ((!selected.length && !current.length) || current === selected) {
          return
        }

        let color = {
          value: selected.data('color'),
          inverted: tinycolor(selected.data('color')).isLight()
            ? '#000'
            : '#fff',
        }

        let checkmark = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="${color.inverted}" role="img" aria-hidden="true" focusable="false"><path d="M18.3 5.6L9.9 16.9l-4.6-3.4-.9 1.2 5.8 4.3 9.3-12.6z"></path></svg>`
        selected.addClass('is-pressed').after(checkmark)
        current.removeClass('is-pressed').parent().find('svg').remove()

        $(this)
          .find('.component-color-indicator')
          .show()
          .attr({
            'aria-label': `(Color: ${color.value || 'Current'})`,
            style: `background: ${color.value || 'Current'};`,
          })
      }

      const onClear = () => {
        $ref = $(this)
        $ref.find('.acf-input li input').attr('checked', false)
        $ref.find('.acf-input li input + label').removeClass('is-pressed')

        $(this).find('.component-color-indicator').addClass('transparent-bg')

        $(this).find('.empty-value').click()
      }

      $(this)
        .find('.components-circular-option-picker__clear')
        .on('click', () => onClear())

      $(this)
        .find('.acf-input li')
        .on('click', () => onChange())

      $(this).find('.component-color-indicator').removeAttr('style')

      onChange()
    })
  })
})(jQuery)
