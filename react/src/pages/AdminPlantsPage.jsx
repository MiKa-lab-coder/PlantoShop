import { useState, useEffect } from 'react';
import { Edit, Trash2 } from 'lucide-react';

function AdminPlantsPage() {
    const [plants, setPlants] = useState([]);
    const [categories, setCategories] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // State pour l'édition
    const [isEditing, setIsEditing] = useState(false);
    const [formData, setFormData] = useState({
        id: null,
        name: '',
        description: '',
        price: '',
        category_id: '',
    });

    // State pour le fichier image
    const [imageFile, setImageFile] = useState(null);

    // Charger les données initiales
    useEffect(() => {
        const fetchData = async () => {
            try {
                const [plantsResponse, categoriesResponse] = await Promise.all([
                    fetch('http://localhost/api/plants'),
                    fetch('http://localhost/api/categories')
                ]);
                if (!plantsResponse.ok || !categoriesResponse.ok) {
                    throw new Error('Impossible de récupérer les données initiales.');
                }
                const plantsData = await plantsResponse.json();
                const categoriesData = await categoriesResponse.json();
                setPlants(plantsData);
                setCategories(categoriesData);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };
        fetchData();
    }, []);

    // Fonction pour mettre à jour les données du formulaire
    const handleInputChange = (e) => {
        const {name, value} = e.target;
        setFormData(prev => ({...prev, [name]: value}));
    };

    // Fonction pour gérer le changement de fichier
    const handleFileChange = (e) => {
        setImageFile(e.target.files[0]);
    };

    // Fonction pour éditer une plante
    const handleEditClick = (plant) => {
        setIsEditing(true);
        setFormData({
            id: plant.id,
            name: plant.name,
            description: plant.description,
            price: plant.price,
            category_id: plant.category.id,
        });
        // Ne pas préremplir le champ fichier
        setImageFile(null);
        window.scrollTo(0, 0);
    };

    // Fonction pour annuler l'édition
    const handleCancelEdit = () => {
        setIsEditing(false);
        setFormData({ id: null, name: '', description: '', price: '', category_id: '' });
        setImageFile(null);
    };

    // Fonction pour soumettre le formulaire
    const handleFormSubmit = async (e) => {
        e.preventDefault();
        
        if (!formData.category_id || formData.category_id === "") {
            alert('Veuillez sélectionner une catégorie.');
            return;
        }

        const submissionData = new FormData();
        submissionData.append('name', formData.name);
        submissionData.append('description', formData.description);
        submissionData.append('price', formData.price);
        submissionData.append('category_id', formData.category_id);
        if (imageFile) {
            submissionData.append('imageFile', imageFile);
        }

        const url = isEditing ? `http://localhost/api/plants/${formData.id}?_method=PUT` : 'http://localhost/api/plants';
        const method = 'POST';

        try {
            const token = localStorage.getItem('token');
            const response = await fetch(url, {
                method,
                headers: { 'Authorization': `Bearer ${token}` },
                body: submissionData,
            });

            if (!response.ok) {
                const errorData = await response.json();
                const errorMessage = errorData.error || 'L\'opération a échoué.';
                throw new Error(errorMessage);
            }

            const savedPlant = await response.json();
            if (isEditing) {
                setPlants(prev => prev.map(p => p.id === savedPlant.id ? savedPlant : p));
            } else {
                setPlants(prev => [...prev, savedPlant]);
            }
            handleCancelEdit();
            alert(`Plante ${isEditing ? 'mise à jour' : 'créée'} !`);
        } catch (err) {
            alert(err.message);
        }
    };

    // Fonction pour supprimer une plante
    const handleDelete = async (plantId) => {
        if (!window.confirm(`Êtes-vous sûr de vouloir supprimer la plante #${plantId} ?`)) return;
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`http://localhost/api/plants/${plantId}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` },
            });
            if (!response.ok) throw new Error('La suppression a échoué.');
            setPlants(prev => prev.filter(p => p.id !== plantId));
            alert('Plante supprimée.');
        } catch (err) {
            alert(err.message);
        }
    };

    if (isLoading) return <div className="text-center p-8">Chargement...</div>;
    if (error) return <div className="text-center p-8 text-red-600">Erreur: {error}</div>;

    return (
        <div className="p-4 md:p-8">
            <div className="mb-8">
                <h2 className="text-2xl font-bold text-slate-800 mb-4">
                    {isEditing ? `Édition de : ${formData.name}` : 'Ajouter une nouvelle plante'}
                </h2>
                <form onSubmit={handleFormSubmit} className="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <input type="text" name="name" placeholder="Nom de la plante" value={formData.name}
                           onChange={handleInputChange} className="w-full p-2 border rounded" required />
                    <textarea name="description" placeholder="Description" value={formData.description}
                              onChange={handleInputChange} className="w-full p-2 border rounded" rows="3" required />
                    
                    {/* Remplacement de l'input text par un input file stylisé */}
                    <div className="flex items-center gap-4">
                        <label htmlFor="imageFile" className="px-4 py-2 bg-green-700 text-white rounded-md
                         cursor-pointer hover:bg-green-600">
                            Choisir une image
                        </label>
                        <input
                            type="file"
                            id="imageFile"
                            name="imageFile"
                            onChange={handleFileChange}
                            className="hidden" /* Masque l'input file natif */
                        />
                        {imageFile ? (
                            <span className="text-slate-700 truncate max-w-[calc(100%-150px)]">{imageFile.name}</span>
                        ) : (
                            <span className="text-slate-500">Aucun fichier choisi</span>
                        )}
                    </div>

                    <div className="flex gap-4">
                        <input type="number" name="price" placeholder="Prix" value={formData.price}
                               onChange={handleInputChange} className="w-1/2 p-2 border rounded" required />
                        <select name="category_id" value={formData.category_id} onChange={handleInputChange}
                                className="w-1/2 p-2 border rounded" required>
                            <option key="placeholder" value="" disabled>-- Choisir une catégorie --</option>
                            {categories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
                        </select>
                    </div>
                    <div className="flex justify-end gap-4">
                        {isEditing && <button type="button" onClick={handleCancelEdit} className="px-4 py-2
                         bg-gray-300 rounded">Annuler</button>}
                        <button type="submit" className="px-4 py-2 bg-green-700 text-white rounded">
                            {isEditing ? 'Mettre à jour' : 'Créer la plante'}
                        </button>
                    </div>
                </form>
            </div>

            <h2 className="text-2xl font-bold text-slate-800 mb-4">Liste des plantes</h2>
            <div className="bg-white rounded-lg shadow-md overflow-x-auto">
                <table className="w-full text-left">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="p-4 font-semibold">ID</th>
                            <th className="p-4 font-semibold">Nom</th>
                            <th className="p-4 font-semibold">Catégorie</th>
                            <th className="p-4 font-semibold">Prix</th>
                            <th className="p-4 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {plants.map(plant => (
                            <tr key={plant.id} className="border-b hover:bg-gray-50">
                                <td className="p-4">{plant.id}</td>
                                <td className="p-4 font-medium">{plant.name}</td>
                                <td className="p-4">{plant.category?.name || 'N/A'}</td>
                                <td className="p-4">{plant.price.toFixed(2)} €</td>
                                <td className="p-4">
                                    <div className="flex justify-center gap-4">
                                        <button onClick={() => handleEditClick(plant)} className="text-blue-600
                                         hover:text-blue-800" title="Éditer"><Edit size={20} /></button>
                                        <button onClick={() => handleDelete(plant.id)}
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

export default AdminPlantsPage;
