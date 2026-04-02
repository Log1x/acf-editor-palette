(function ($) {
  if (typeof acf.add_action === 'undefined') {
    return
  }

  const tinycolor = require('tinycolor2')

  acf.add_action('new_field/type=editor_palette', function ($field, context) {
    initializeField($field);
  });

  function initializeField($field) {

    const onChange = () => {
      let current = $field.find('.acf-input li input + label.is-pressed')
      let selected = $field.find('.acf-input li input:checked + label')

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

      current
        .removeClass('is-pressed')
        .parent()
        .find('svg')
        .remove()
      selected.addClass('is-pressed').after(checkmark)

      $field
        .find('.component-color-indicator')
        .show()
        .attr({
          'aria-label': `(Color: ${color.value || 'Current'})`,
          style: `background: ${color.value || 'Current'};`,
        })
    }

    const onClear = () => {
      $ref = $field;
      $ref.find('.acf-input li input').attr('checked', false)
      $ref.find('.acf-input li input + label')
        .removeClass('is-pressed')
        .parent()
        .find('svg')
        .remove()

      $field
        .find('.component-color-indicator')
        .hide()

      $field
        .find('.empty-value')
        .click();
    }

    $field
      .find('.acf-label label .component-color-indicator')
      .remove()

    $field
      .find('.acf-label label')
      .append(
        `<span class="component-color-indicator" style="display: none;"></span>`
      )

    $field
      .find('.components-circular-option-picker__clear')
      .on('click', () => onClear())

    $field
      .find('.acf-input li')
      .on('click', () => onChange())

    onChange()
  }

})(jQuery)
