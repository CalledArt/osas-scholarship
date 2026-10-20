import Alpine from 'alpinejs';
import { injectSpeedInsights } from '@vercel/speed-insights';
import documentUploads from './document-uploads';

window.Alpine = Alpine;

Alpine.data('documentUploads', documentUploads);

Alpine.start();

injectSpeedInsights();
