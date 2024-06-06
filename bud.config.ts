import type { Bud } from '@roots/bud'

export default async (bud: Bud) => {
  bud.setPath('@src', 'assets')
  bud.setPath('@dist', 'public')

  bud.entry(['field.js', 'field.css'])
}
