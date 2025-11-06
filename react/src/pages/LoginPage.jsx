import { useState } from 'react';
import { useNavigate, Link, useOutletContext } from 'react-router-dom'; // Importer useOutletContext
import { AtSign, Lock, LogIn } from 'lucide-react';

function LoginPage() {
  // Récupérer handleLogin depuis le contexte de l'Outlet
  const { handleLogin } = useOutletContext();

  // Etat initial du formulaire
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // Fonction de navigation
  const navigate = useNavigate();

  // Fonction de soumission du formulaire
  const handleSubmit = async (event) => {
    event.preventDefault();
    setIsLoading(true);
    setError(null);

    // Consommateur de l'API
    try {
      const response = await fetch('http://localhost/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Email ou mot de passe incorrect.');
      }

      // IMPORTANT, recuperation et stockage du token
      if (data.token) {
        localStorage.setItem('token', data.token);
        // Appel de handeLogin pour mettre à jour l'état de connexion
        handleLogin();
        navigate('/');
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-[80vh]">
      <div className="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <div className="flex justify-center items-center gap-2">
          <LogIn className="text-green-600" size={28} />
          <h2 className="text-2xl font-bold text-center text-slate-800">
            Connexion
          </h2>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Champ Email */}
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <AtSign className="text-slate-400" size={20} />
            </div>
            <input
              type="email"
              placeholder="adresse@email.com"
              className="w-full pl-10 p-2 border border-slate-300 rounded-md text-slate-800 focus:ring-2
               focus:ring-green-500 focus:border-green-500"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              disabled={isLoading}
            />
          </div>

          {/* Champ Mot de passe */}
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Lock className="text-slate-400" size={20} />
            </div>
            <input
              type="password"
              placeholder="Mot de passe"
              className="w-full pl-10 p-2 border border-slate-300 rounded-md text-slate-800 focus:ring-2
               focus:ring-green-500 focus:border-green-500"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              disabled={isLoading}
            />
          </div>

          {error && <div className="text-red-600 text-sm text-center">{error}</div>}

          <div>
            <button
              type="submit"
              className="w-full py-2 px-4 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700
               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:bg-green-400"
              disabled={isLoading}
            >
              {isLoading ? 'Connexion en cours...' : 'Se connecter'}
            </button>
          </div>
        </form>

        {/* Redirection vers la page d'inscription */}
        <div className="text-center mt-6">
          <span className="text-sm text-slate-600">Pas encore inscrit ? </span>
          <Link to="/register" className="text-sm font-medium text-green-600 hover:underline">
            Créer un compte
          </Link>
        </div>

      </div>
    </div>
  );
}

export default LoginPage;