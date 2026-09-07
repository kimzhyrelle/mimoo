const steps = ['Details', 'Adress', 'Verification', 'Security'];

const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

export default function StepProgress({ currentStep }: { currentStep: number }) {
    return (
        <div className="mt-6" style={fontSFCompact}>
            <style>{`
                @keyframes fillProgress {
                    from { width: 0%; }
                    to   { width: 100%; }
                }
                .fill-progress {
                    animation: fillProgress 0.5s ease-out forwards;
                }
            `}</style>
            <div className="flex gap-8">
                {steps.map((label, i) => {
                    const stepNumber = i + 1;
                    const isCompleted = stepNumber < currentStep;
                    const isActive = stepNumber === currentStep;

                    return (
                        <div key={label} className="flex-1 flex flex-col gap-2">
                            <div className="h-1.5 rounded-full bg-gray-200 overflow-hidden">
                                {(isActive || isCompleted) && (
                                    <div
                                        key={isActive ? `fill-${currentStep}` : `fill-done-${label}`}
                                        className={`h-full rounded-full ${
                                            isActive ? 'bg-gray-900 fill-progress' : 'bg-purple-300 w-full'
                                        }`}
                                    />
                                )}
                            </div>
                            <span
                                className={`text-sm ${
                                    isActive || isCompleted ? 'text-gray-900 font-bold' : 'text-gray-400'
                                }`}
                            >
                                {stepNumber} {label}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}