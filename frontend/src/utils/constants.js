/**
 * Status labels and badge styling.
 * Keys mirror App\Enums\ProjectStatus on the backend.
 */
export const STATUS_MAP = {
  draft: { label: 'Draft', color: 'gray', bgClass: 'bg-gray-100', textClass: 'text-gray-700' },
  submitted: { label: 'Diajukan', color: 'sky', bgClass: 'bg-sky-100', textClass: 'text-sky-800' },
  in_review: { label: 'Sedang Dinilai', color: 'amber', bgClass: 'bg-amber-100', textClass: 'text-amber-800' },
  approved: { label: 'Disetujui', color: 'brand', bgClass: 'bg-brand-100', textClass: 'text-brand-800' },
  revised: { label: 'Perlu Revisi', color: 'accent', bgClass: 'bg-accent-100', textClass: 'text-accent-800' },
  rejected: { label: 'Ditolak', color: 'red', bgClass: 'bg-red-100', textClass: 'text-red-800' },
};

/**
 * Priority labels.
 * Keys mirror App\Enums\ProjectPriority — the API only accepts low|normal|high.
 */
export const PRIORITY_MAP = {
  low: { label: 'Rendah', bgClass: 'bg-gray-100', textClass: 'text-gray-700' },
  normal: { label: 'Normal', bgClass: 'bg-sky-100', textClass: 'text-sky-800' },
  high: { label: 'Tinggi', bgClass: 'bg-accent-100', textClass: 'text-accent-800' },
};

/** Options for priority selects, kept in sync with PRIORITY_MAP. */
export const PRIORITY_OPTIONS = [
  { value: 'low', label: 'Rendah' },
  { value: 'normal', label: 'Normal' },
  { value: 'high', label: 'Tinggi' },
];

/** Options for status filters, kept in sync with STATUS_MAP. */
export const STATUS_OPTIONS = Object.entries(STATUS_MAP).map(([value, { label }]) => ({ value, label }));

export const ALLOWED_FILE_TYPES = [
  'application/pdf',
  'image/jpeg',
  'image/png',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

export const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
