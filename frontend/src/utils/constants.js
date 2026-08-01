export const STATUS_MAP = {
  draft: { label: 'Draft', color: 'gray', bgClass: 'bg-gray-100', textClass: 'text-gray-800' },
  submitted: { label: 'Submitted', color: 'blue', bgClass: 'bg-blue-100', textClass: 'text-blue-800' },
  in_review: { label: 'In Review', color: 'yellow', bgClass: 'bg-yellow-100', textClass: 'text-yellow-800' },
  approved: { label: 'Approved', color: 'green', bgClass: 'bg-green-100', textClass: 'text-green-800' },
  revised: { label: 'Revised', color: 'orange', bgClass: 'bg-orange-100', textClass: 'text-orange-800' },
  rejected: { label: 'Rejected', color: 'red', bgClass: 'bg-red-100', textClass: 'text-red-800' }
};

export const PRIORITY_MAP = {
  normal: { label: 'Normal', color: 'gray' },
  high: { label: 'High', color: 'orange' },
  urgent: { label: 'Urgent', color: 'red' }
};

export const ALLOWED_FILE_TYPES = [
  'application/pdf',
  'image/jpeg',
  'image/png',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

export const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
