

import Alpine from 'alpinejs';
import lmsFileUpload from './lms-file-upload';
import lmsSingleFileUpload from './lms-single-file-upload';

window.Alpine = Alpine;

Alpine.data('lmsFileUpload', lmsFileUpload);
Alpine.data('lmsSingleFileUpload', lmsSingleFileUpload);

Alpine.start();
