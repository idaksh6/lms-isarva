

import Alpine from 'alpinejs';
import lmsFileUpload from './lms-file-upload';
import lmsSingleFileUpload from './lms-single-file-upload';
import lmsThemePicker from './lms-theme-picker';
import lmsUserGuide from './lms-user-guide';
import lmsQaThread from './lms-qa-thread';
import lmsQaBoard from './lms-qa-board';
import './lms-calendar-scroll';

window.Alpine = Alpine;

Alpine.data('lmsFileUpload', lmsFileUpload);
Alpine.data('lmsSingleFileUpload', lmsSingleFileUpload);
Alpine.data('lmsThemePicker', lmsThemePicker);
Alpine.data('lmsUserGuide', lmsUserGuide);
Alpine.data('lmsQaThread', lmsQaThread);
Alpine.data('lmsQaBoard', lmsQaBoard);

Alpine.start();
