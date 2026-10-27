import Alpine from 'alpinejs';
import { injectSpeedInsights } from '@vercel/speed-insights';
import documentUploads from './document-uploads';
import appShell from './app-shell';

window.Alpine = Alpine;

Alpine.data('documentUploads', documentUploads);
Alpine.data('appShell', appShell);

Alpine.start();

injectSpeedInsights();
