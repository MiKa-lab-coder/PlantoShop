import { useState, useEffect } from 'react';
import { Edit, Trash2 } from 'lucide-react';

function AdminUsersPage() {
    const [users, setUsers] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // --- États pour le formulaire ---
    const [isEditing, setIsEditing] = useState(false);
    const [formData, setFormData] = useState({
        id: null,
        firstName: '',
        lastName: '',
        email: '',
        password: '',
    });

    // Récupération des données initiales
    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) throw new Error('Accès non autorisé.');

                const response = await fetch('http://localhost/api/users', {
                    headers: { 'Authorization': `Bearer ${token}` },
                });
                if (!response.ok) {
                    throw new Error('Impossible de récupérer la liste des utilisateurs.');
                }
                const data = await response.json();
                setUsers(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };
        fetchUsers();
    }, []);

    // --- Gestion du formulaire ---
    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleEditClick = (user) => {
        setIsEditing(true);
        setFormData({
            id: user.id,
            firstName: user.firstName,
            lastName: user.lastName,
            email: user.email,
            password: '', // On ne pré-remplit jamais le mot de passe
        });
        window.scrollTo(0, 0);
    };

    const handleCancelEdit = () => {
        setIsEditing(false);
        setFormData({ id: null, firstName: '', lastName: '', email: '', password: '' });
    };

    const handleFormSubmit = async (e) => {
        e.preventDefault();
        const url = isEditing ? `http://localhost/api/users/${formData.id}` : 'http://localhost/api/register';
        const method = isEditing ? 'PUT' : 'POST';

        const dataToSubmit = { ...formData };
        if (isEditing && !dataToSubmit.password) {
            delete dataToSubmit.password;
        }

        try {
            const token = localStorage.getItem('token');
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify(dataToSubmit),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'L\'opération a échoué.');
            }

            const savedUser = await response.json();
            if (isEditing) {
                setUsers(prev => prev.map(u => u.id === savedUser.id ? savedUser : u));
            } else {
                setUsers(prev => [...prev, savedUser]);
            }
            handleCancelEdit();
            alert(`Utilisateur ${isEditing ? 'mis à jour' : 'créé'} !`);
        } catch (err) {
            alert(err.message);
        }
    };

    // --- Gestion de la suppression ---
    const handleDelete = async (userId) => {
        if (!window.confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur #${userId} ?`)) return;
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`http://localhost/api/users/${userId}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` },
            });
            if (!response.ok) throw new Error('La suppression a échoué.');
            setUsers(prev => prev.filter(u => u.id !== userId));
            alert('Utilisateur supprimé.');
        } catch (err) {
            alert(err.message);
        }
    };

    if (isLoading) return <div className="text-center p-8">Chargement...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;

    return (
        <div className="p-4 md:p-8">
            {/* --- Section du Formulaire --- */}
            <div className="mb-8">
                <h2 className="text-2xl font-bold text-slate-800 mb-4">
                    {isEditing ? `Édition de : ${formData.firstName} ${formData.lastName}` : 'Ajouter un nouvel utilisateur'}
                </h2>
                <form onSubmit={handleFormSubmit} className="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <div className="flex gap-4">
                        <input type="text" name="firstName" placeholder="Prénom" value={formData.firstName}
                               onChange={handleInputChange} className="w-1/2 p-2 border rounded" required />
                        <input type="text" name="lastName" placeholder="Nom" value={formData.lastName}
                               onChange={handleInputChange} className="w-1/2 p-2 border rounded" required />
                    </div>
                    <input type="email" name="email" placeholder="Email" value={formData.email}
                           onChange={handleInputChange} className="w-full p-2 border rounded" required />
                    <input type="password" name="password" placeholder={isEditing ? "Nouveau mot de passe (optionnel)"
                        : "Mot de passe"} value={formData.password} onChange={handleInputChange}
                           className="w-full p-2 border rounded" required={!isEditing} />
                    <div className="flex justify-end gap-4">
                        {isEditing && <button type="button" onClick={handleCancelEdit} className="px-4 py-2
                         bg-gray-300 rounded">Annuler</button>}
                        <button type="submit" className="px-4 py-2 bg-green-600 text-white rounded">
                            {isEditing ? 'Mettre à jour' : 'Créer l\'utilisateur'}
                        </button>
                    </div>
                </form>
            </div>

            {/* --- Section de la Liste --- */}
            <h2 className="text-2xl font-bold text-slate-800 mb-4">Liste des utilisateurs</h2>
            <div className="bg-white rounded-lg shadow-md overflow-x-auto">
                <table className="w-full text-left">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="p-4 font-semibold">ID</th>
                            <th className="p-4 font-semibold">Nom</th>
                            <th className="p-4 font-semibold">Email</th>
                            <th className="p-4 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map(user => (
                            <tr key={user.id} className="border-b hover:bg-gray-50">
                                <td className="p-4">{user.id}</td>
                                <td className="p-4 font-medium">{user.firstName} {user.lastName}</td>
                                <td className="p-4">{user.email}</td>
                                <td className="p-4">
                                    <div className="flex justify-center gap-4">
                                        <button onClick={() => handleEditClick(user)} className="text-blue-600
                                         hover:text-blue-800" title="Éditer"><Edit size={20} />
                                        </button>
                                        <button onClick={() => handleDelete(user.id)}
                                                className="text-red-600 hover:text-red-800" title="Supprimer">
                                            <Trash2 size={20} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default AdminUsersPage;
