import {useEffect, useState} from "react";
import {useNavigate} from "react-router-dom";
import {Pen, User, Mail, Home, Phone, Trash2} from "lucide-react";

function UserSettingsPage() {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    const [formState, setFormState] = useState({
        id: null,
        firstName: '',
        lastName: '',
        email: '',
        address: '',
        phoneNumber: '',
    });

    // Récupérer les infos du compte de l'utilisateur au chargement
    useEffect(() => {
        const fetchUser = async () => {
            const token = localStorage.getItem('token');
            if (!token) {
                setError('Vous devez être connecté.');
                setIsLoading(false);
                return;
            }
            try {
                const response = await fetch('http://localhost/api/user/profile', {
                    headers: {'Authorization': `Bearer ${token}`},
                });
                if (!response.ok) {
                    throw new Error('Impossible de récupérer les informations du profil.');
                }
                const data = await response.json();
                setFormState({
                    id: data.id,
                    firstName: data.firstName || '',
                    lastName: data.lastName || '',
                    email: data.email || '',
                    address: data.address || '',
                    phoneNumber: data.phoneNumber || '',
                });
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };
        fetchUser();
    }, []);

    const handleChange = (e) => {
        const {name, value} = e.target;
        setFormState(prevState => ({...prevState, [name]: value}));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!formState.id) return;
        setIsLoading(true);
        setError(null);

        try {
            const response = await fetch(`http://localhost/api/users/${formState.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
                body: JSON.stringify(formState),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Erreur lors de la mise à jour du profil.');
            }
            alert('Profil mis à jour avec succès !');
        } catch (err) {
            setError(err.message);
        } finally {
            setIsLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!formState.id) return;

        if (!window.confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
            return;
        }

        setIsLoading(true);
        setError(null);

        try {
            const response = await fetch(`http://localhost/api/users/${formState.id}`, {
                method: 'DELETE',
                headers: {'Authorization': `Bearer ${localStorage.getItem('token')}`},
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Erreur lors de la suppression du compte.');
            }

            localStorage.removeItem('token');
            alert('Compte supprimé avec succès.');
            navigate('/');
            window.location.reload();

        } catch (err) {
            setError(err.message);
        } finally {
            setIsLoading(false);
        }
    };

    if (isLoading) return <p>Chargement...</p>;
    if (error) return <p className="text-red-500">{error}</p>;

    return (
        <div>
            <h1 className="text-3xl font-bold text-slate-800 mb-8">Mise à jour du profil</h1>
            <form onSubmit={handleSubmit} className="bg-white p-8 rounded-lg shadow-md space-y-6">
                <div className="flex items-center gap-4">
                    <div className="flex items-center">
                        <User className="text-green-700" size={24}/>
                    </div>
                    <input name="firstName" type="text" placeholder="Prénom" value={formState.firstName}
                           onChange={handleChange} className="w-full p-2 border rounded"/>
                    <input name="lastName" type="text" placeholder="Nom" value={formState.lastName}
                           onChange={handleChange} className="w-full p-2 border rounded"/>
                </div>
                <div className="flex items-center gap-4">
                    <div className="flex items-center">
                        <Mail className="text-green-700" size={24}/>
                    </div>
                    <input name="email" type="email" placeholder="Adresse email" value={formState.email}
                           onChange={handleChange} className="w-full p-2 border rounded"/>
                </div>
                <div className="flex items-center gap-4">
                    <div className="flex items-center">
                        <Home className="text-green-700" size={24}/>
                    </div>
                    <input name="address" type="text" placeholder="Adresse" value={formState.address}
                           onChange={handleChange} className="w-full p-2 border rounded"/>
                </div>
                <div className="flex items-center gap-4">
                    <div className="flex items-center">
                        <Phone className="text-green-700" size={24}/>
                    </div>
                    <input name="phoneNumber" type="text" placeholder="Téléphone" value={formState.phoneNumber}
                           onChange={handleChange} className="w-full p-2 border rounded"/>
                </div>
                <div className="flex gap-4 pt-4">
                    <button type="submit" className="w-full py-2 px-4 bg-green-700 text-white font-semibold
                     rounded-md hover:bg-green-600 disabled:bg-green-400 flex items-center justify-center gap-2"
                            disabled={isLoading}>
                        <Pen size={20}/>
                        {isLoading ? 'Mise à jour...' : 'Mettre à jour'}
                    </button>
                    <button type="button" className="w-full py-2 px-4 bg-red-700 text-white font-semibold
                     rounded-md hover:bg-red-800 disabled:bg-red-400 flex items-center justify-center gap-2"
                            onClick={handleDelete} disabled={isLoading}>
                        <Trash2 size={20}/>
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    );
}

export default UserSettingsPage;
