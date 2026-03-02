import React, { useState } from 'react';

const PLACEHOLDER_URL = 'https://placehold.co/400x400/e2e8f0/94a3b8?text=No+Image';

export default function ImageWithFallback({ src, alt = '', className = '', ...props }) {
    const [error, setError] = useState(false);

    const handleError = () => setError(true);
    const imgSrc = error || !src ? PLACEHOLDER_URL : src;

    return (
        <img
            src={imgSrc}
            alt={alt}
            className={className}
            onError={handleError}
            {...props}
        />
    );
}
