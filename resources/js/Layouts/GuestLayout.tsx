import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex h-screen">
            <div className="flex w-full min-w-72 max-w-screen-md flex-col p-10 md:w-[40vw]">
                <h1 className="text-2xl font-bold">HaeBot ERP</h1>
                {children}
            </div>
            <div className="group relative hidden flex-1 overflow-hidden md:block">
                <img
                    aria-hidden="true"
                    alt=""
                    src="https://i.ibb.co.com/CVnHpnX/andrii-solok-n5-SAdno3y7-Q-unsplash.jpg"
                    decoding="async"
                    data-nimg="fill"
                    loading="lazy"
                    className="size-full object-cover object-center transition duration-500 group-hover:blur-sm"
                />
                <div className="absolute inset-0 bg-black opacity-50 transition duration-500 group-hover:opacity-60"></div>
            </div>
        </div>
    );
}
