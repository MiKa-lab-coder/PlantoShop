import {useNavigate} from "react-router-dom";
import {useState} from "react";
import {SendHorizontal} from 'lucide-react';
import { API_URL } from '../services/api.js';

function ContactPage() {
    const [formdata, setFormdata] = useState({
        name: '',
        email: '',
        message: '',
    });
    const [error, setError] = useState(null);
    const [successMessage, setSuccessMessage] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    // Fonction de navigation
    const navigate = useNavigate();

    // Fonction de changement de valeur du formulaire
    const handleChange = (e) => {
        const {name, value} = e.target;
        setFormdata(prevState => ({
            ...prevState,
            [name]: value,
        }));
    };

    // Fonction de soumission du formulaire
    const handleSubmit = async (event) => {
        event.preventDefault();
        setIsLoading(true);
        setError(null);
        setSuccessMessage(null);

        // Consommateur de l'API
        try {
            // Api fictive de contact
            const response = await fetch(`${API_URL}/api/contact`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formdata),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = data.errors.join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Erreur lors de l\'envoi du message.');
            }

            // Afficher le message de succès et vider le formulaire
            setSuccessMessage('Message envoyé avec succès ! Vous allez être redirigé.');

            // Réinitialiser le formulaire
            setFormdata({name: '', email: '', message: ''});

            // Redirection après 3 secondes
            setTimeout(() => {
                navigate('/');
            }, 3000);

        } catch (err) {
            setError(err.message);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="flex items-center justify-center min-h-[80vh]">
            <div className="w-full max-w-lg p-8 bg-white rounded-lg shadow-md">
                <div className="flex justify-center items-center gap-2">
                    <h2 className="text-2xl font-bold text-center text-green-700 mb-8">Contactez-nous</h2>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <input name="name" type="text" placeholder="Nom"
                           value={formdata.name} onChange={handleChange}
                           required disabled={isLoading}
                           className="w-full p-2 border border-slate-300 rounded-md"/>
                    <input name="email" type="email" placeholder="Adresse email"
                           value={formdata.email} onChange={handleChange}
                           required disabled={isLoading}
                           className="w-full p-2 border border-slate-300 rounded-md"/>
                    <textarea name="message" placeholder="Message"
                              value={formdata.message} onChange={handleChange}
                              required disabled={isLoading}
                              className="w-full p-2 border border-slate-300 rounded-md"/>

                    {error && <div className="text-red-600 text-sm text-center whitespace-pre-line">{error}</div>}
                    {successMessage && <div className="text-green-600 text-sm text-center">{successMessage}</div>}

                    <div>
                        <button type="submit" disabled={isLoading || successMessage} className="w-full py-2 px-4
                         bg-green-700 text-white
                         font-semibold rounded-md hover:bg-green-600 disabled:bg-green-400 flex items-center
                          justify-center gap-2">
                            <SendHorizontal size={20}/>
                            {isLoading ? 'Envoi en cours...' : 'Envoyer'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default ContactPage;