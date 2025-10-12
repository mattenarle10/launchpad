/**
 * Main API Export - Central point for all API modules
 */

import AuthAPI from './auth.js';
import StudentAPI from './student.js';
import CompanyAPI from './company.js';
import CDCAPI from './cdc.js';

export {
    AuthAPI,
    StudentAPI,
    CompanyAPI,
    CDCAPI
};

// Default export for convenience
export default {
    Auth: AuthAPI,
    Student: StudentAPI,
    Company: CompanyAPI,
    CDC: CDCAPI
};

