import type { Bud } from '@roots/bud'

export default async (bud: Bud) => {
  bud
    .setPath('@src', 'assets')
    .setPath('@dist', 'public')
    .entry(['field.js', 'field.css'])
    .hash()
}
