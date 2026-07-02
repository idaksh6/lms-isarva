

import Alpine from 'alpinejs';
import lmsFileUpload from './lms-file-upload';
import lmsSingleFileUpload from './lms-single-file-upload';
import lmsThemePicker from './lms-theme-picker';

window.Alpine = Alpine;

Alpine.data('lmsFileUpload', lmsFileUpload);
Alpine.data('lmsSingleFileUpload', lmsSingleFileUpload);
Alpine.data('lmsThemePicker', lmsThemePicker);

Alpine.start();
