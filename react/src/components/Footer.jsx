function Footer() {
  return (
    <footer className="bg-slate-800 text-white mt-12 py-8 px-8">
      <div className="container mx-auto text-center">
        <p>&copy; {new Date().getFullYear()} PlantoShop. Tous droits réservés.</p>
        <div className="flex justify-center gap-4 mt-4">
          <a href="#" className="hover:text-green-400">Conditions Générales</a>
          <a href="#" className="hover:text-green-400">Politique de Confidentialité</a>
        </div>
      </div>
    </footer>
  );
}

export default Footer;
