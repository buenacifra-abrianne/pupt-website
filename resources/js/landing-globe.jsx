import React from 'react';
import { createRoot } from 'react-dom/client';
import { Globe } from '@/components/magicui/globe';

const container = document.getElementById('landing-globe');
if (container) createRoot(container).render(<Globe decorative />);
