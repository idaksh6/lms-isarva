

import Alpine from 'alpinejs';
import lmsFileUpload from './lms-file-upload';

window.Alpine = Alpine;

Alpine.data('lmsFileUpload', lmsFileUpload);

Alpine.start();
