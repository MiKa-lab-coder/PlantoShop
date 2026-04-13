import { useState, useEffect } from 'react';
import { User, Mail, Home, Phone } from 'lucide-react';
import { API_URL } from '../services/api.js';

function UserProfilePage() {
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchUserProfile = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    throw new Error('Vous devez être connecté pour voir votre profil.');
                }

                const response = await fetch(`${API_URL}/api/user/profile`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error('Impossible de récupérer les informations du profil.');
                }
                const data = await response.json();
                setUser(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchUserProfile();
    }, []);

    if (isLoading) return <div className="text-center p-8">Chargement de votre profil...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;
    if (!user) return <div className="text-center p-8">Profil non trouvé.</div>;

    return (
        <div>
            <h1 className="text-3xl font-bold text-slate-800 mb-8">Mon Profil</h1>
            <div className="bg-white p-8 rounded-lg shadow-md space-y-6">
                <div className="flex items-center gap-4">
                    <User className="text-green-700" size={24} />
                    <p className="text-lg"><span className="font-semibold">Nom:</span> {user.firstName} {user.lastName}</p>
                </div>
                <div className="flex items-center gap-4">
                    <Mail className="text-green-700" size={24} />
                    <p className="text-lg"><span className="font-semibold">Email:</span> {user.email}</p>
                </div>
                <div className="flex items-center gap-4">
                    <Home className="text-green-700" size={24} />
                    <p className="text-lg"><span className="font-semibold">Adresse:</span> {user.address || 'Non renseignée'}</p>
                </div>
                <div className="flex items-center gap-4">
                    <Phone className="text-green-700" size={24} />
                    <p className="text-lg"><span className="font-semibold">Téléphone:</span> {user.phoneNumber || 'Non renseigné'}</p>
                </div>
            </div>
        </div>
    );
}

export default UserProfilePage;
