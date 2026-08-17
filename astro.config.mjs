import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  site: 'https://talkintree.com',
  integrations: [tailwind({ applyBaseStyles: false })]
});
