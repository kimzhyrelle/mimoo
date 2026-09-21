import { useState, useEffect } from 'react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

type Props = {
    data: any;
    setData: (field: string, value: any) => void;
    errors: any;
    onBack: () => void;
    onNext: () => void;
};

interface Region {
    code: string;
    name: string;
    regionName: string;
}

interface Province {
    code: string;
    name: string;
    regionCode: string;
}

interface CityMunicipality {
    code: string;
    name: string;
    provinceCode: string;
}

interface Barangay {
    code: string;
    name: string;
    cityCode: string;
}

export default function StepAddress({ data, setData, errors, onBack, onNext }: Props) {
    const [provinces, setProvinces] = useState<Province[]>([]);
    const [cities, setCities] = useState<CityMunicipality[]>([]);
    const [barangays, setBarangays] = useState<Barangay[]>([]);
    const [loading, setLoading] = useState({ provinces: false, cities: false, barangays: false });

    // Load provinces on mount
    useEffect(() => {
        setLoading(prev => ({ ...prev, provinces: true }));
        fetch('https://psgc.gitlab.io/api/provinces/')
            .then(res => res.json())
            .then((data: Province[]) => {
                setProvinces(data.sort((a, b) => a.name.localeCompare(b.name)));
                setLoading(prev => ({ ...prev, provinces: false }));
            })
            .catch(() => setLoading(prev => ({ ...prev, provinces: false })));
    }, []);

    // Load cities/municipalities when province changes
    useEffect(() => {
        if (data.province) {
            const selectedProvince = provinces.find(p => p.name === data.province);
            if (selectedProvince) {
                setLoading(prev => ({ ...prev, cities: true }));
                fetch(`https://psgc.gitlab.io/api/provinces/${selectedProvince.code}/cities-municipalities/`)
                    .then(res => res.json())
                    .then((data: CityMunicipality[]) => {
                        setCities(data.sort((a, b) => a.name.localeCompare(b.name)));
                        setLoading(prev => ({ ...prev, cities: false }));
                    })
                    .catch(() => setLoading(prev => ({ ...prev, cities: false })));
            }
        } else {
            setCities([]);
            setData('municipality_city', '');
        }
    }, [data.province, provinces]);

    // Load barangays when city/municipality changes
    useEffect(() => {
        if (data.municipality_city) {
            const selectedCity = cities.find(c => c.name === data.municipality_city);
            if (selectedCity) {
                setLoading(prev => ({ ...prev, barangays: true }));
                fetch(`https://psgc.gitlab.io/api/cities-municipalities/${selectedCity.code}/barangays/`)
                    .then(res => res.json())
                    .then((data: Barangay[]) => {
                        setBarangays(data.sort((a, b) => a.name.localeCompare(b.name)));
                        setLoading(prev => ({ ...prev, barangays: false }));
                    })
                    .catch(() => setLoading(prev => ({ ...prev, barangays: false })));
            }
        } else {
            setBarangays([]);
            setData('barangay', '');
        }
    }, [data.municipality_city, cities]);

    return (
        <>
            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-2 tracking-wider" style={fontSyncopate}>
                Step 2 of 4 • Address
            </p>

            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-3 tracking-wider" style={fontSFCompact}>
                Address
            </p>

            <div className="grid grid-cols-3 gap-4" style={fontSFCompact}>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Province<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <select
                        value={data.province}
                        onChange={(e) => setData('province', e.target.value)}
                        disabled={loading.provinces}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300 bg-white disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                    >
                        <option value="">
                            {loading.provinces ? 'Loading provinces...' : 'Select province'}
                        </option>
                        {provinces.map((province) => (
                            <option key={province.code} value={province.name}>
                                {province.name}
                            </option>
                        ))}
                    </select>
                    {errors.province && <p className="text-red-500 text-xs mt-1">{errors.province}</p>}
                </div>

                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Municipality / City<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <select
                        value={data.municipality_city}
                        onChange={(e) => setData('municipality_city', e.target.value)}
                        disabled={!data.province || loading.cities}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300 bg-white disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                    >
                        <option value="">
                            {!data.province
                                ? 'Select province first'
                                : loading.cities
                                ? 'Loading cities...'
                                : 'Select municipality/city'}
                        </option>
                        {cities.map((city) => (
                            <option key={city.code} value={city.name}>
                                {city.name}
                            </option>
                        ))}
                    </select>
                    {errors.municipality_city && (
                        <p className="text-red-500 text-xs mt-1">{errors.municipality_city}</p>
                    )}
                </div>

                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Barangay<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <select
                        value={data.barangay}
                        onChange={(e) => setData('barangay', e.target.value)}
                        disabled={!data.municipality_city || loading.barangays}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300 bg-white disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                    >
                        <option value="">
                            {!data.municipality_city
                                ? 'Select municipality first'
                                : loading.barangays
                                ? 'Loading barangays...'
                                : 'Select barangay'}
                        </option>
                        {barangays.map((barangay) => (
                            <option key={barangay.code} value={barangay.name}>
                                {barangay.name}
                            </option>
                        ))}
                    </select>
                    {errors.barangay && <p className="text-red-500 text-xs mt-1">{errors.barangay}</p>}
                </div>
            </div>

            <div className="mt-4" style={fontSFCompact}>
                <label className="text-sm font-semibold text-gray-900">
                    Street & house number<span className="text-red-500 ml-0.5">*</span>
                </label>
                <input
                    type="text"
                    placeholder="e.g 123 Mabini St."
                    value={data.street_address}
                    onChange={(e) => setData('street_address', e.target.value)}
                    className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                />
                {errors.street_address && <p className="text-red-500 text-xs mt-1">{errors.street_address}</p>}
            </div>

            <div className="flex gap-3 mt-8" style={fontSFCompact}>
                <button
                    type="button"
                    onClick={onBack}
                    className="px-8 border border-gray-300 rounded-xl py-3 font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                    Back
                </button>
                <button
                    type="button"
                    onClick={onNext}
                    className="flex-1 bg-[#3B1F6B] text-white rounded-xl py-3 font-medium hover:bg-[#2E1854] transition"
                >
                    Next
                </button>
            </div>
        </>
    );
}