import { useState, useEffect } from 'react';
import { useNavigate, Link, useOutletContext } from 'react-router-dom';
import { ShoppingCart, Trash2, Plus, Minus } from 'lucide-react';

function CartPage() {
    const { isLoggedIn } = useOutletContext();
    const navigate = useNavigate();

    const [cartItems, setCartItems] = useState([]);

    // Charger le panier depuis le localStorage au montage du composant
    useEffect(() => {
        const localCart = JSON.parse(localStorage.getItem('cart') || '[]');
        setCartItems(localCart);
    }, []);

    // Fonction pour sauvegarder le panier dans le localStorage et mettre à jour l'état
    const updateCart = (newCartItems) => {
        localStorage.setItem('cart', JSON.stringify(newCartItems));
        setCartItems(newCartItems);
    };

    // Fonction pour mettre à jour la quantité d'un article dans le panier
    const handleUpdateQuantity = (plantId, newQuantity) => {
        let newCart;
        if (newQuantity < 1) {
            // Si la quantité est inférieure à 1, on supprime l'article
            newCart = cartItems.filter(item => item.plant.id !== plantId);
        } else {
            newCart = cartItems.map(item =>
                item.plant.id === plantId ? { ...item, quantity: newQuantity } : item
            );
        }
        updateCart(newCart);
    };

    // Fonction pour supprimer un article du panier
    const handleRemoveItem = (plantId) => {
        const newCart = cartItems.filter(item => item.plant.id !== plantId);
        updateCart(newCart);
    };

    // Fonction pour calculer le total du panier
    const calculateTotal = () => {
        return cartItems.reduce((total, item) => total + item.plant.price * item.quantity, 0);
    };

    // Fonction pour valider et payer
    const handleCheckout = async () => {
        if (!isLoggedIn) {
            // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
            navigate('/login');
            return;
        }

        // Si l'utilisateur est connecté, on envoie le panier à l'API pour créer la commande
        try {
            const token = localStorage.getItem('token');
            const response = await fetch('http://localhost/api/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({ items: cartItems }),
            });

            if (!response.ok) {
                throw new Error('La création de la commande a échoué.');
            }

            // La commande est créée, on vide le panier local et on redirige
            localStorage.removeItem('cart');
            setCartItems([]);
            navigate('/user/orders'); // Redirection vers l'historique des commandes

        } catch (error) {
            //console.error('Erreur lors de la commande:', error);
            alert(error.message);
        }
    };

    // Affichage du panier
    const cartTotal = calculateTotal();

    return (
        <div className="min-h-[calc(100vh-160px)] w-full p-4">
            <div className="max-w-4xl mx-auto">
                <h2 className="text-3xl font-bold text-green-700 mb-6 text-center">Mon panier</h2>
                
                {cartItems.length === 0 ? (
                    <div className="mt-8 p-6 bg-white rounded-lg shadow-md text-center">
                        <ShoppingCart className="mx-auto mb-4 text-green-700" size={48} />
                        <p className="text-xl text-slate-700">Votre panier est vide.</p>
                        <Link to="/shop" className="mt-4 inline-block px-6 py-2 bg-green-700 text-white rounded
                         hover:bg-green-600">
                            Retourner à la boutique
                        </Link>
                    </div>
                ) : (
                    <div className="mt-8 w-full bg-white p-6 rounded-lg shadow-md">
                        {cartItems.map(item => (
                            <div key={item.plant.id} className="flex items-center justify-between border-b pb-4 mb-4">
                                <div className="flex items-center gap-4">
                                    {/* Note: item.plant.imageUrl doit être fourni par l'API des plantes */}
                                    <img src={item.plant.imageUrl || 'https://via.placeholder.com/64'} alt={item.plant.name}
                                         className="w-16 h-16 object-cover rounded"/>
                                    <div>
                                        <h3 className="text-lg font-semibold text-slate-800">{item.plant.name}</h3>
                                        <p className="text-slate-600">Prix: {item.plant.price.toFixed(2)} €</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-4">
                                    <button onClick={() => handleUpdateQuantity(item.plant.id, item.quantity - 1)}
                                            className="p-2 bg-gray-200 rounded hover:bg-gray-300">
                                        <Minus size={16} />
                                    </button>
                                    <span className="text-slate-800 w-8 text-center">{item.quantity}</span>
                                    <button onClick={() => handleUpdateQuantity(item.plant.id, item.quantity + 1)}
                                            className="p-2 bg-gray-200 rounded hover:bg-gray-300">
                                        <Plus size={16} />
                                    </button>
                                    <button onClick={() => handleRemoveItem(item.plant.id)} className="p-2
                                     text-red-600 rounded hover:bg-red-100">
                                        <Trash2 size={20} />
                                    </button>
                                </div>
                            </div>
                        ))}
                        <div className="mt-6 text-right">
                            <p className="text-xl font-bold text-slate-800">Total: {cartTotal.toFixed(2)} €</p>
                            <button onClick={handleCheckout} className="mt-4 px-6 py-3 bg-green-700
                             text-white font-semibold rounded-lg hover:bg-green-800">
                                {isLoggedIn ? 'Valider et Payer' : 'Se connecter pour commander'}
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

export default CartPage;