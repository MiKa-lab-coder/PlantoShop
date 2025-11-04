import {useState} from 'react';
import {useNavigate, Link} from 'react-router-dom';
import { UserPlus } from 'lucide-react';

function RegisterPage() {
    // On utilise un seul état pour tout le formulaire
    const [formData, setFormData] = useState({
        firstName: '',
        lastName: '',
        address: '',
        phoneNumber: '',
        email: '',
        password: '',
        passwordConfirmation: '',
    });

    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();

    // Fonction de changement de valeur du formulaire
    const handleChange = (e) => {
        const {name, value} = e.target;
        setFormData(prevState => ({
            ...prevState,
            [name]: value,
        }));
    };
    // Fonction de soumission du formulaire
    const handleSubmit = async (event) => {
        event.preventDefault();
        setIsLoading(true);
        setError(null);

        // Vérification du mot de passe
        if (formData.password !== formData.passwordConfirmation) {
            setError("Les mots de passe doivent être identiques.");
            setIsLoading(false);
            return;
        }

        // Consommateur de l'API
        try {
            const response = await fetch('http://localhost/api/register', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    firstName: formData.firstName,
                    lastName: formData.lastName,
                    address: formData.address,
                    phoneNumber: formData.phoneNumber,
                    email: formData.email,
                    password: formData.password,
                }),
            });

            const data = await response.json();
            // Debug
            //console.log(data)

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = data.errors.join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Erreur lors de l\'inscription.');
            }

            //console.log('Inscription réussie:', data);
            navigate('/login');

        } catch (err) {
            setError(err.message);
            //console.error('Erreur d\'inscription:', err);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="flex items-center justify-center min-h-[80vh]">
            <div className="w-full max-w-lg p-8 space-y-6 bg-white rounded-lg shadow-md">
                <div className="flex justify-center items-center gap-2">
                    <UserPlus className="text-green-600" size={28}/>
                    <h2 className="text-2xl font-bold text-center text-slate-800">Créer un compte</h2>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="flex flex-col md:flex-row gap-4">
                        <input name="firstName" type="text" placeholder="Prénom" value={formData.firstName}
                               onChange={handleChange}
                               required disabled={isLoading} className="w-full p-2 border border-slate-300 rounded-md"/>
                        <input name="lastName" type="text" placeholder="Nom" value={formData.lastName}
                               onChange={handleChange}
                               required disabled={isLoading} className="w-full p-2 border border-slate-300 rounded-md"/>
                    </div>
                    <input name="address" type="text" placeholder="Adresse" value={formData.address}
                           onChange={handleChange} required disabled={isLoading}
                           className="w-full p-2 border border-slate-300 rounded-md"/>
                    <input name="phoneNumber" type="tel" placeholder="Téléphone" value={formData.phoneNumber}
                           onChange={handleChange} required disabled={isLoading}
                           className="w-full p-2 border border-slate-300 rounded-md"/>
                    <input name="email" type="email" placeholder="Adresse email" value={formData.email}
                           onChange={handleChange}
                           required disabled={isLoading} className="w-full p-2 border border-slate-300 rounded-md"/>
                    <input name="password" type="password" placeholder="Mot de passe (8 caractères min.)"
                           value={formData.password} onChange={handleChange} required disabled={isLoading}
                           className="w-full p-2 border border-slate-300 rounded-md"/>
                    <input name="passwordConfirmation" type="password" placeholder="Confirmer le mot de passe"
                           value={formData.passwordConfirmation} onChange={handleChange} required disabled={isLoading}
                           className="w-full p-2 border border-slate-300 rounded-md"/>

                    {error && <div className="text-red-600 text-sm text-center whitespace-pre-line">{error}</div>}

                    <div>
                        <button type="submit" disabled={isLoading} className="w-full py-2 px-4 bg-green-600 text-white
                         font-semibold rounded-md hover:bg-green-700 disabled:bg-green-400 flex items-center
                          justify-center gap-2">
                            <UserPlus size={20}/>
                            {isLoading ? 'Création en cours...' : 'Créer mon compte'}
                        </button>
                    </div>
                </form>

                <div className="text-center mt-6">
                    <span className="text-sm text-slate-600">Déjà un compte ? </span>
                    <Link to="/login" className="text-sm font-medium text-green-600 hover:underline">Se connecter</Link>
                </div>
            </div>
        </div>
    );
}

export default RegisterPage;
